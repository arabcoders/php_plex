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

    /**
     * Check File path.
     *
     * @param string|\SplFileObject $file
     * @return bool
     */
    public function isMatch($file): bool
    {
        $filePath = $file instanceof \SplFileObject ? $file->getPath() : $file;

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