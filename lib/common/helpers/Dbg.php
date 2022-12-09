<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

use Yii;

class Dbg
{

    private static function export($var)
    {
        if (is_object($var)) {
            try {
                return var_export($var, true);
            } catch (\Exception $e) {
                return \yii\helpers\VarDumper::export($var);
            }
        } else {
            return \yii\helpers\VarDumper::export($var);
        }
    }

    public static function log($msg)
    {
        \Yii::info($msg, 'dbg/log');
    }

    public static function logVar($var, $msg = 'var')
    {
        self::log($msg . '=' . self::export($var));
    }

    public static function logQuery($var, $msg = 'query')
    {
        if ($var instanceof \yii\db\query) {
            $var = $var->createCommand()->getRawSql();
        }
        self::logVar($var, $msg);
    }

    public static function echo($msg)
    {
        if (\common\helpers\System::isDevelopment()) {
            echo "<pre>" . $msg . '</pre>';
        }
        self::log($msg);
    }

    public static function echoVar($var, $msg = 'var')
    {
        self::echo($msg . '=' . self::export($var));
        self::logVar($var, $msg);
    }

    public static function echoQuery($var, $msg = 'query')
    {
        if ($var instanceof \yii\db\query) {
            $var = $var->createCommand()->getRawSql();
        }
        self::echoVar($var, $msg);
    }

    private static function getFileName($fn, $ext)
    {
        \common\helpers\Assert::match('/^[-\w]+$/', $fn, 'Invalid file name');
        $fn = Yii::getAlias('@app/runtime/logs/') . $fn;

        if (file_exists($fn . $ext)) {
            $fn .= date(' Y-m-d H-i-s ').microtime();
        }
        return $fn . $ext;
    }

    public static function saveVar($var, $fn = 'var')
    {
        $content = self::export($var);
        self::saveText($content, $fn, '.vardump');
    }

    public static function saveJson($var, $fn = 'var')
    {
        $content = json_encode($var,  JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES );
        self::saveText($content, $fn, '.json');
    }

    public static function saveText($txt, $fn = 'var', $ext = '.txt')
    {
        $fn = self::getFileName($fn, $ext);
        if (false === file_put_contents($fn, $txt)) {
            \Yii::warning('Error in file_put_contents: '  . error_get_last(), 'debug/save');
        }
    }

    public static function logStack($msg = 'Stack')
    {
        self::logVar(self::getStack(), $msg);
    }

    public static function echoStack($msg = 'Stack')
    {
        self::echoVar(self::getStack(), $msg);
    }

    public static function getStack()
    {
        ob_start();
        debug_print_backtrace(/*DEBUG_BACKTRACE_IGNORE_ARGS*/);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    static $timeFirst = null;
    static $time = null;

    private static function timeStart($msg)
    {
        self::$time = self::$timeFirst = microtime(true);
        if (!empty($msg)) {
            self::log('Timer started' . $msg);
        }
    }

    public static function logTime($msg = '')
    {
        if (is_null(self::$time)) {
            self::timeStart($msg);
        } else {
            $cur = microtime(true);
            $msg = empty($msg)? '' : ($msg . '. ');
            self::log( sprintf('%sElapsed: %.3f (since start %.3f)', $msg, $cur-self::$time, $cur-self::$timeFirst) );
            self::$time = microtime(true);
        }
    }
}
