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

class PregFilter implements IFilter
{
    private $matched = '';

    private $matchAll = [];

    private $hasMatched = false;

    private $status;

    private $filter;

    private $line;

    public function __construct(string $pattren)
    {
        $this->filter = $pattren;
    }

    public function run(string $line): IFilter
    {
        $this->line = $line;

        $this->status = @preg_match($this->filter, $line, $matches);

        if (1 === $this->status) {
            $this->hasMatched = true;
            $this->matchAll = $matches ?? [];
            $this->matched = $matches[1] ?? '';
        } else {
            $this->hasMatched = false;
        }

        return $this;
    }

    public function isFound(): bool
    {
        return $this->hasMatched;
    }

    public function getFirst(): string
    {
        return $this->matched;
    }

    public function getAll(): array
    {
        return $this->matchAll;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function __toString()
    {
        return $this->getFirst();
    }

}