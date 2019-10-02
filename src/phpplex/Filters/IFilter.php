<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex\Filters;

/**
 * Interface IFilter
 *
 * @package src\Filters
 */
interface IFilter
{
    /**
     * Register Regular expression.
     *
     * @param string $pattren
     */
    public function __construct(string $pattren);

    /**
     * Run pattern on provided line.
     *
     * @param string $line
     * @return IFilter
     */
    public function run(string $line): IFilter;

    /**
     * Whether we found a match.
     *
     * @return bool
     */
    public function isFound(): bool;

    /**
     * Get first match.
     *
     * @return string
     */
    public function getFirst(): string;

    /**
     * Get All Matches.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get the line that was matched aganist.
     *
     * @return string
     */
    public function getLine(): string;

    /**
     * @return string
     */
    public function __toString();
}