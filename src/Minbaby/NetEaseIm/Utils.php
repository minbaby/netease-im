<?php

namespace Minbaby\NetEaseIm;

use ArrayAccess;

class Utils
{
    /**
     * @param      $array
     * @param      $key
     * @param null $default
     *
     * @return array
     */
    public static function arrayGet($array, $key, $default = null)
    {
        if (! static::isArray($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (static::isArray($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    public static function isArray($array)
    {
        return is_array($array) || $array instanceof ArrayAccess;
    }

    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    public static function boolConvertToString($bool)
    {
        return true === $bool ? 'true' : 'false';
    }

    public static function arrCheckAndPush($arr, $key, $value)
    {
        if (! empty($value)) {
            return array_merge($arr, [$key => $value]);
        }

        return $arr;
    }
}
