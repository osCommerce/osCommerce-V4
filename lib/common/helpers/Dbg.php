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

    public static function log($msg)
    {
        \Yii::info($msg, 'dbg/log');
    }

    public static function logVar($var, $msg = 'var')
    {
        self::log($msg . '=' . \yii\helpers\VarDumper::export($var));
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
        self::echo($msg . '=' . \yii\helpers\VarDumper::export($var));
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
        $content = \yii\helpers\VarDumper::export($var);
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
    
    public static function getStack()
    {
        ob_start();
        debug_print_backtrace(/*DEBUG_BACKTRACE_IGNORE_ARGS*/);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

}
