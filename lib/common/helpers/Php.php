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

class Php
{
    public static function str_start_with($haystack, $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    public static function array_key_first($arr)
    {
        if (empty($arr) || !is_array($arr)) return null;
        if (!function_exists('array_key_first')) { // PHP < 7.3
            foreach($arr as $key => $unused) {
                return $key;
            }
        } else {
            return array_key_first($arr);
        }
    }

    public static function arrayGetSubArrayKeyBySubValue($array, $subArrayKey, $value)
    {
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                if (is_array($item) && isset($item[$subArrayKey]) && $item[$subArrayKey] == $value) {
                    return $key;
                }
            }
        }
    }

    public static function arrayGetSubArrayBySubValue($array, $subArrayKey, $value, $default = null)
    {
        if (!is_null($key = self::arrayGetSubArrayKeyBySubValue($array, $subArrayKey, $value))) {
            return $array[$key];
        }
    }

    /**
     * exec fucntion may be disabled on shared hostings
     * @param string $command
     * @param array|null $output
     * @param int|null $result_code
     * @return void
     */
    public static function exec(string $command, array &$output = null, int &$result_code = null)
    {
        if (function_exists('exec')) {
            return @exec($command, $output, $result_code);
        } else {
            $output = null;
            \Yii::warning('function exec is disabled on this hosting');
            return false;
        }
    }

    public static function sprintfSafe(string $msg)
    {
        return self::vsprintfSafe($msg, array_slice(func_get_args(), 1));
    }

    public static function vsprintfSafe(string $msg, array $args)
    {
        try {
            $res = vsprintf($msg, $args);
        } catch(\Throwable $e) {
            $res = sprintf("Error: %s for msg=%s, args=\n%s", $e->getMessage(), $msg, \yii\helpers\VarDumper::export($args));
        }
        return $res;
    }

    public static function logError($exception, $prefix = null)
    {
        $prefix = empty($prefix)? '' : "$prefix: ";
        \Yii::warning($prefix . $exception->getMessage() . "\n" . $exception->getTraceAsString());
    }

    public static function handleErrorProd($exception, $prefix = null)
    {
        if (\common\helpers\System::isProduction()) {
            self::logError($exception, $prefix);
        } else {
            throw $exception;
        }
    }

    public static function throwOrLog($errMessage)
    {
        if (\common\helpers\System::isProduction()) {
            \Yii::warning($errMessage . "\n" . \common\helpers\Dbg::getStack());
        } else {
            throw new \Exception($errMessage);
        }
    }

    public static function strInsertSpacesBeforeCapitalChar($str)
    {
        return ltrim(preg_replace('/[A-Z]/', ' $0', $str));
    }

}