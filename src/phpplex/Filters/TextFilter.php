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

class TextFilter extends PregFilter
{
    /**
     * Register Text filter for example "foo|boo|baz".
     *
     * @param string $pattren
     */
    public function __construct(string $pattren)
    {
        parent::__construct('#(' . preg_quote($pattren, '#') . ')#');
    }
}