<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex;

use Symfony\Component\Console\Application;

/**
 * @author Abdulmohsen A. <admin@arabcoders.rog>
 */
class PHPPlex extends Application
{
    private const APP_NAME = 'PHPPlex';
    private const VERSION = '1.0.0-RC';
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct(static::APP_NAME, static::VERSION);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function getDefaultCommands()
    {
        return array_merge(
            parent::getDefaultCommands(),
            [
                new Commands\Run,
                new Commands\Scan,
            ]
        );
    }
}