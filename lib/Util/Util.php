<?php

namespace Beautyalist\Util;

abstract class Util
{
    public static function isList($array)
    {
        if (!\is_array($array)) {
            return false;
        }
        if (\is_array($array) && 0 == count($array)) {
            return true;
        }
        if (\array_keys($array) !== \range(0, \count($array) - 1)) {
            return false;
        }

        return true;
    }

    public static function utf8($value)
    {
        if (!\function_exists('mb_detect_encoding')) {
            \trigger_error('Please enabled mbstring extension.', \E_USER_WARNING);
        } else {
            if ('UTF-8' !== \mb_detect_encoding($value, 'UTF-8', true)) {
                $value = \utf8_encode($value);
            }
        }

        return $value;
    }

    public static function encodeParameters($params)
    {
        $all    = self::prepareParamsArray($params);
        $pieces = [];
        foreach ($all as $param) {
            list($key, $value) = $param;
            $pieces[]          = self::urlEncode($key).'='.self::urlEncode($value);
        }

        return \implode('&', $pieces);
    }

    public static function prepareParamsArray($params, $parentKey = null)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $calculatedKey = $parentKey ? "{$parentKey}[{$key}]" : $key;

            if (self::isList($value)) {
                $result = \array_merge($result, self::prepareParams($value, $calculatedKey));
            } elseif (\is_array($value)) {
                $result = \array_merge($result, self::prepareParamsArray($value, $calculatedKey));
            } else {
                \array_push($result, [$calculatedKey, $value]);
            }
        }

        return $result;
    }

    public static function prepareParams($currentValue, $calculatedKey)
    {
        $result = [];

        foreach ($currentValue as $key => $value) {
            if (self::isList($value)) {
                $result = \array_merge($result, self::prepareParams($value, $calculatedKey));
            } elseif (\is_array($value)) {
                $result = \array_merge($result, self::prepareParamsArray($value, "{$calculatedKey}[{$key}]"));
            } else {
                \array_push($result, ["{$calculatedKey}[{$key}]", $value]);
            }
        }

        return $result;
    }

    public static function urlEncode($key)
    {
        return \str_replace(['%5B', '%5D'], ['[', ']'], \urlencode((string) $key));
    }
}
