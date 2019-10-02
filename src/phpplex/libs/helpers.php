<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Read ENV variable.
 *
 * @param string $key
 * @param mixed $default default null.
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = getenv($key);

    if (false === $value) {
        return $default instanceof Closure ? $default() : $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    if (($valueLength = strlen($value)) > 1 && strpos($value, '"') === 0 && $value[$valueLength - 1] === '"') {
        return substr($value, 1, -1);
    }

    return $value;
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param string $key
 * @param mixed $default
 * @param ArrayAccess|array $list
 * @return mixed
 */
function get($key, $default = null, $list = [])
{
    if (!accessible($list)) {
        return value($default);
    }
    if (null === $key) {
        return $list;
    }
    if (exists($list, $key)) {
        return $list[$key];
    }
    if (strpos($key, '.') === false) {
        return $list[$key] ?? value($default);
    }
    foreach (explode('.', $key) as $segment) {
        if (accessible($list) && exists($list, $segment)) {
            $list = $list[$segment];
        } else {
            return value($default);
        }
    }
    return $list;
}

/**
 * Determine whether the given value is array or instance of ArrayAccess.
 *
 * @param mixed $value
 * @return bool
 */
function accessible($value)
{
    return is_array($value) || $value instanceof ArrayAccess;
}

/**
 * Determine if the given key exists in the provided array.
 *
 * @param ArrayAccess|array $array
 * @param string|int $key
 * @return bool
 */
function exists($array, $key)
{
    if ($array instanceof ArrayAccess) {
        return $array->offsetExists($key);
    }
    return array_key_exists($key, $array);
}

/**
 * Return the default value of the given value.
 *
 * @param mixed $value
 * @return mixed
 */
function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}
