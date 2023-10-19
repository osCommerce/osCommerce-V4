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

class DbgNull
{
    public static function __callStatic($name, $arguments) {}
}


class Dbg
{

    public static function defineConsts()
    {
        //$s= \common\models\Configuration::findOne(['configuration_key' => 'DEFINE_DBG_CONST'])->configuration_value ?? '';
        $str = defined('DEFINE_DBG_CONST') ? DEFINE_DBG_CONST : '';
        if (!empty($str)) {
            foreach (explode(',', $str) as $constName) {
                $name = 'DBG_' . $constName;
                defined($name) or define($name, true);
            }
        }
    }


    /**
     * @param $debugConst - part of debug constant
     * @return Dbg
     */
    public static function ifDefined($debugConst)
    {
        if (!empty($debugConst) && defined('DBG_'.$debugConst) && constant('DBG_'.$debugConst) === true) {
            return self::class;
        }
        return DbgNull::class;
    }

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

    private static function exportVarArray($vars)
    {
        $res = '';
        foreach ($vars as $var) {
            if (!empty($res)) {
                $res .= (is_object($var) || is_array($var)) ? "\n" : ',';
            }
            $res .= self::export($var);
        }
        return $res;
    }

    public static function log($msg)
    {
        if (class_exists('\Yii')) {
            $tmp = \Yii::$app->log->traceLevel;
            \Yii::$app->log->traceLevel = 0;
            \Yii::info($msg, 'dbg/log');
            \Yii::$app->log->traceLevel = $tmp;
        }
    }

    public static function logf($msg)
    {
        self::log(\common\helpers\Php::vsprintfSafe($msg, array_slice(func_get_args(), 1)));
    }

    public static function logVar($var, $msg = 'var')
    {
        self::log($msg . '=' . self::export($var));
    }

    public static function logVars()
    {
        self::log(self::exportVarArray(func_get_args()));
    }

    public static function logQuery($var, $msg = 'query')
    {
        if ($var instanceof \yii\db\query) {
            $var = $var->createCommand()->getRawSql();
        }
        self::logVar($var, $msg);
        return $var;
    }

    public static function echo($msg)
    {
        if (!class_exists('\common\helpers\System') || \common\helpers\System::isDevelopment() || \common\helpers\System::isConsole()) {
            echo "<pre>" . $msg . '</pre>';
        }
        self::log($msg);
    }

    public static function out($msg, $dest)
    {
        if (method_exists(self::class, $dest)) {
            self::$dest($msg);
        }
    }

    public static function echoVar($var, $msg = 'var')
    {
        self::echo($msg . '=' . self::export($var));
        self::logVar($var, $msg);
    }

    public static function echoVars()
    {
        $vars = self::exportVarArray(func_get_args());
        self::echo($vars);
        self::logVar($vars);
    }

    public static function echoQuery($var, $msg = 'query')
    {
        if ($var instanceof \yii\db\query) {
            $var = $var->createCommand()->getRawSql();
        }
        self::echoVar($var, $msg);
        return $var;
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

    public static function loadText($fn, $ext)
    {
        $res = file_get_contents(Yii::getAlias('@app/runtime/logs/') . $fn . $ext);
        if (false === $res) {
            \Yii::warning('Error in file_get_contents: '  . (error_get_last()['message']??'Unknown'), 'debug/save');
        }
        return $res;
    }
    public static function loadJson($fn, $associative = null)
    {
        $txt = self::loadText($fn, '.json');
        return json_decode($txt, $associative);
    }

    public static function logStack($msg = 'Stack', $full = false)
    {
        self::logVar(self::getStack($full), $msg);
    }

    public static function echoStack($msg = 'Stack', $full = false)
    {
        self::echoVar(self::getStack($full), $msg);
    }

    public static function getStack($full = false)
    {
        ob_start();
        debug_print_backtrace($full? 0 : DEBUG_BACKTRACE_IGNORE_ARGS);
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
            return 0;
        } else {
            $cur = microtime(true);
            $msg = empty($msg)? '' : ($msg . '. ');
            $elapsed = $cur-self::$time;
            self::log( sprintf('%sElapsed: %.3f (since start %.3f)', $msg, $elapsed, $cur-self::$timeFirst) );
            self::$time = microtime(true);
            return $elapsed;
        }
    }

    static $timeLoop = [];
    private const LOOP_GLOBAL =  '__loop-global__';

    public static function timeLoopStart($loopName = 'loop1')
    {
        self::$timeLoop[$loopName][self::LOOP_GLOBAL]['cur_time'] = microtime(true);
    }

    public static function timeLoop($msg, $loopName = 'loop1')
    {
        if (isset(self::$timeLoop[$loopName][self::LOOP_GLOBAL]['cur_time'])) {
            $curInterval = microtime(true) - self::$timeLoop[$loopName][self::LOOP_GLOBAL]['cur_time'];
        } else {
            $curInterval = 0;
            self::log("Interval for $loopName/$msg can't be calculated. Use ::timeLoopStart() befoe ::timeLoop()");
        }
        self::$timeLoop[$loopName][$msg]['time'] = (self::$timeLoop[$loopName][$msg]['time']??0) + $curInterval;
        self::$timeLoop[$loopName][$msg]['count'] = (self::$timeLoop[$loopName][$msg]['count']??0) + 1;
        self::$timeLoop[$loopName][self::LOOP_GLOBAL]['cur_time'] = microtime(true);
        return $curInterval;
    }

    public static function timeLoopLog($loopName = 'loop1')
    {
        if (is_array(self::$timeLoop[$loopName] ?? null)) {
            $s = "Loop $loopName:\n";
            foreach(self::$timeLoop[$loopName] as $name => $val) {
                if ($name != self::LOOP_GLOBAL) {
                    $s .= sprintf("%s=%.3f (%d times)\n", $name, $val['time'], $val['count']);
                }
            }
            self::log($s);
        } else {
            self::log("Loop $loopName does not contains items");
        }
    }

    public static function timeLoopLogAll()
    {
        foreach (self::$timeLoop as $loopName => $val) {
            self::timeLoopLog($loopName);
        }
    }

    private static $mem = null;
    private static $memReal = null;
    private static $memCount = 0;
    public static function outMem($msg = '', $force = false, $dest = 'log')
    {
        $newReal = memory_get_peak_usage(false);
        $new = memory_get_peak_usage(true);
        if (is_null(self::$mem)) {
            self::$memReal = $newReal;
            self::$mem = $new;
        } else {
            if ($force || $newReal != self::$memReal || $new != self::$mem) {
                if (!empty($msg)) $msg .= ': ';
                $realStr = $force? '=' . memory_get_usage(true) : '';
                self::out( sprintf('%sMax=%d (+%d) Real%s+%d Count=%d', $msg, $new, $new-self::$mem, $realStr, $newReal-self::$memReal, self::$memCount), $dest);
                self::$memCount = 0;
                self::$mem = $new;
                self::$memReal = $newReal;
            } else {
                self::$memCount++;
            }
        }

    }

}
