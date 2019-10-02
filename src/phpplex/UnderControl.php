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

class UnderControl
{
    private $locations;

    private $directory = '';

    private $section = 0;

    public function __construct(array $locations)
    {
        $this->locations = $locations;
    }

    public function isMatch(\SplFileObject $file): bool
    {
        $filePath = $file->getPath();

        foreach ($this->locations as $path) {

            if (false !== stripos($filePath, $path['path'])) {

                $this->section = $path['id'];
                $this->directory = $filePath;

                return true;
            }

        }

        return false;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getSection(): int
    {
        return $this->section;
    }
}