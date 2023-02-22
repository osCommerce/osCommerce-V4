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

}