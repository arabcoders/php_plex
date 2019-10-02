<?php declare(strict_types=1, ticks=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex;

/**
 * Class Plex
 *
 * @package src
 */
class Plex
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool
     */
    private $ssl;

    /**
     * @var string
     */
    private $token;

    private $locations = [];
    private $sections = [];

    protected $lastCommand = '';

    public function __construct(string $host, int $port, string $token, bool $ssl = false)
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Curl Extension is not loaded.');
        }

        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
        $this->token = $token;
    }

    public function getLocations(): array
    {
        $this->getSections();

        return $this->locations;
    }

    public function getSections(): array
    {
        $response = $this->call('/library/sections');

        if (empty($response['Directory']) || !is_array($response['Directory'])) {
            throw new \RuntimeException(
                'Plex did not return any directory information you may not have setup your library.',
                0
            );
        }

        foreach ($response['Directory'] as $section) {

            $id = $section['key'];

            $this->sections[$id] = [
                'key' => $id,
                'uuid' => $section['uuid'] ?? null,
                'language' => $section['language'] ?? null,
                'title' => $section['title'] ?? null,
                'type' => $section['type'] ?? null,
                'scanner' => $section['scanner'] ?? null,
                'agent' => $section['agent'] ?? null,
                'paths' => [$section['Location'] ?? []],
            ];

            foreach ($this->sections[$id]['paths'] ?? [] as $path) {
                $this->locations[] = [
                    'id' => (int)$path['id'],
                    'parent' => $id,
                    'path' => $path['path'],
                ];
            }
        }

        return $this->sections;
    }

    /**
     * Get the last api call.
     * @return string
     */
    public function getLastCommand(): string
    {
        return $this->lastCommand;
    }

    /**
     * Call Plex Endpoints.
     *
     * @param $path
     * @param array $params
     * @param string $method
     * @return array
     */
    private function call(string $path, array $params = [], string $method = 'GET'): array
    {
        $fullUrl = $this->ssl ? 'https://' : 'http://';
        $fullUrl .= $this->host . ':' . $this->port . $path;

        if (!empty($params)) {
            $fullUrl .= '?' . http_build_query($params);
        }

        $this->lastCommand = $fullUrl;

        $options = [
            \CURLOPT_URL => $fullUrl,
            \CURLOPT_AUTOREFERER => true,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_CONNECTTIMEOUT => 30,
            \CURLOPT_TIMEOUT => 30,
            \CURLOPT_FAILONERROR => true,
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_HTTPHEADER => [
                'X-Plex-Token: ' . $this->token,
            ],
        ];

        switch (strtoupper($method)) {
            case 'POST':
                $options[\CURLOPT_POST] = true;
                break;
            case'GET':
                break;
            default:
                $options[\CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        $request = curl_init();

        curl_setopt_array($request, $options);

        $response = curl_exec($request);

        if (!$response) {

            $errNo = curl_errno($request);
            $errMsg = curl_error($request);

            if (is_resource($request)) {
                curl_close($request);
            }

            throw new \RuntimeException($errMsg ?? '', $errNo);
        }

        if (is_resource($request)) {
            curl_close($request);
        }

        $parsed = [];

        $this->normalizeSimpleXML(simplexml_load_string($response), $parsed);

        return $parsed;
    }

    /**
     * Normalize SimpleXML object
     *
     * @param $xmlObject
     * @param $result
     */
    private function normalizeSimpleXML($xmlObject, &$result): void
    {
        $data = $xmlObject;

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                $this->normalizeSimpleXML($value, $res);
                if (($key === '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $data;
        }
    }

}