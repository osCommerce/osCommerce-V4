<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

class Assert {

    public static function error(string $message)
    {
        throw new \Exception($message);
    }

    public static function assert($condition, string $message = null)
    {
        if (!$condition) {
            static::error($message ?? 'Assertion failed');
        }
    }

    protected static function ident($obj)
    {
        $type = gettype($obj);
        if (is_object($obj)) {
            $value = get_class($obj);
        } elseif (is_array($obj)) {
            $value = var_export($obj, true);
        } else {
            $value = (string)$obj;
        }
        return sprintf('%s(%s)', $type, $value);
    }

    protected static function errorMsg($message, $message_def)
    {
        $message = $message ?? "Assertion: %s";
        static::error( sprintf($message, $message_def) );
    }

    public static function isSet(bool $isSet, $varName, string $message = null)
    {
        if (!$isSet) {
            static::errorMsg($message, 'variable "$varName" is not set');
        }
    }

    /**
     * @deprecated Use isNotNull
     */
    public static function assertNotNull($value, string $message = null)
    {
        self::isNotNull($value, $message);
    }

    public static function isNotNull($value, string $message = null)
    {
        if (is_null($value)) {
            static::errorMsg($message, 'unexpected value: null');
        }
    }

    /**
     * @deprecated Use isNotEmpty
     */
    public static function assertNotEmpty($value, string $message = null)
    {
        self::isNotEmpty($value, $message);
    }

    public static function isNotEmpty($value, string $message = null)
    {
        if (empty($value)) {
            static::errorMsg($message, 'unexpected value: empty');
        }
    }

    public static function isEmpty($value, string $message = null)
    {
        if (!empty($value)) {
            static::errorMsg($message, 'expect empty value, but given: ' . self::ident($value) );
        }
    }

    public static function hasMethod($obj, $method, string $message = null)
    {
        if (!method_exists($obj, $method)) {
            $def = sprintf("%s has not method %s", self::ident($obj), self::ident($method));
            static::errorMsg($message, $def);
        }
    }

    public static function stringMatched($str, string $match, string $message = null)
    {
        if (!preg_match($match, $str)) {
            $def = sprintf("%s does not match %s", self::ident($str), self::ident($match));
            static::errorMsg($message, $def);
        }
    }

    public static function fileExists($fn, string $message = null)
    {
        if (!file_exists($fn)) {
            $def = sprintf("file %s does not exist", self::ident($fn));
            static::errorMsg($message, $def);
        }
    }

    public static function isObject($obj, string $message = null)
    {
        if (!is_object($obj)) {
            $def = sprintf("%s is not object", self::ident($obj));
            static::errorMsg($message, $def);
        }
    }

    public static function instanceOf($obj, $class, string $message = null)
    {
        if (!($obj instanceof $class)) {
            $def = sprintf("%s is not instance of %s", self::ident($obj), self::ident($class));
            static::errorMsg($message, $def);
        }
    }

    public static function isArray($arr, string $message = null)
    {
        if (!is_array($arr)) {
            $def = sprintf("%s is not array", self::ident($arr));
            static::errorMsg($message, $def);
        }
    }

    public static function keyExists($arr, $key, string $message = null)
    {
        static::isArray($arr, $message);
        if (!(isset($arr[$key]) || \array_key_exists($key, $arr))) {
            $def = sprintf("Key %s is not exist in %s", self::ident($key), self::ident($arr));
            static::errorMsg($message, $def);
        }
    }

    public static function match($pattern, $value, string $message = null)
    {
        if (!preg_match($pattern, $value)) {
            $def = sprintf("%s is not match template", self::ident($value));
            static::errorMsg($message, $def);
        }
    }

    public static function notImplemented(string $message = null)
    {
        static::errorMsg($message, 'Not implemented yet');
    }
}
