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

class Helper
{ 

    public static function getIdentAR($ar)
    {
        if ($ar instanceof \yii\db\ActiveRecord) {
            $key = $ar->getPrimaryKey();
            $key = is_array($key) ? implode('-', array_filter($key, "is_string")) : $key;
            return $ar->tablename() . '(' . $key . ')';
        } elseif (is_object($ar)) {
            return $ar::classname();
        } else {
            return (string) $ar;
        }
    }

    public static function sumCols(&$sum, $add)
    {
        if (empty($sum)) {
            $sum = $add;
        } else {
            foreach($add as $key=>$val) {
                $sum[$key] += $val;
            }
        }
    }

    public static function formatArr(string $format, array $arr)
    {
        $temp_arr = [];
        array_walk($arr, function (&$value,$key) use (&$temp_arr) {
            $temp_arr["{".$key."}"] = $value;
        } );
        return strtr($format, $temp_arr);
    }


    public static function getFeedName($feed)
    {
        return \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_ENTITY_' . strtoupper($feed) );
    }
    
    public static function getGroupName($group)
    {
        return \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_GROUP_' . strtoupper($group) );
    }

    public static function getFeedGroupInfo($feed)
    {
        foreach (\common\extensions\OscLink\OscLink::FEED_GROUPS as $group => $feeds) {
            if (($index = array_search($feed, $feeds)) !== false) {
                return ['group' => $group, 'index' => $index, 'count' => count($feeds)];
            }
        }
        throw new \Exception("Feed $feed is not included in group");
    }

    public static function ProgressAndLog($msg)
    {
        \OscLink\Progress::Log($msg);
        \OscLink\Logger::print($msg);
    }

}