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

namespace OscLink;

use \common\helpers\Assert;

class Progress
{
    private static $progress_last_update = 0;
    private static $last_not_important_params = null;

    public static $percent_prev_stage = 0;
    public static $percent_in_cur_stage = 100;

    private static function getTextDef($value)
    {
        return "$value%";
    }

    private static function isImportantMsg($params)
    {
        return !empty($params['reset']) || !empty($params['message']) ||
            self::$progress_last_update == 0 || (microtime(true) - self::$progress_last_update) > 1;

    }

    public static function Update(array $params)
    {
        if (self::isImportantMsg($params)) {
            self::$progress_last_update = microtime(true);

            // update progress if it was delayed as not important
            if (!array_key_exists('progress', $params) && is_array(self::$last_not_important_params) && array_key_exists('progress', self::$last_not_important_params)) {
                $params['progress'] = self::$last_not_important_params['progress'];
                $params['text'] = self::$last_not_important_params['text'] ?? self::getTextDef($params['progress']);
            }

            echo ('<script>window.parent.doProgressUpdate(' . json_encode($params) . ')</script>'
                . "\n" . str_repeat(' ', (1024 * 8 * 4)) . "\n"
            );
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        } else {
            if (array_key_exists('progress', $params)) {
                self::$last_not_important_params = $params;
            }
        }
    }


    public static function Log($msg)
    {
        self::Update(['message' => $msg]);
    }

    public static function Percent(int $value, $txt = null)
    {
        $value = $value > 100 ? 100 : $value;
        if (self::$percent_in_cur_stage != 100) {
            $value = intval( self::$percent_prev_stage + ($value * self::$percent_in_cur_stage / 100) );
        }

        $txt = $txt ?? self::getTextDef($value);
        self::Update([
            'progress' => $value,
            'text' => $txt,
        ]);
    }

    public static function Done($success = true)
    {
        $arr = ['reset' => true];
        if ($success) {
            $arr['progress'] = 100;
            $arr['text'] = '100%';
        } 
        self::Update($arr);
    }

    public static function showLogFile($format = 'Detailed log: %s <a href="extensions?module=OscLink&action=adminActionShowLog&log=%s" target="_blank">view</a>')
    {
        $fn = \OscLink\Logger::get()->getFilename();
        \OscLink\Progress::Log( sprintf($format, $fn, basename($fn, '.log')) );
    }

}