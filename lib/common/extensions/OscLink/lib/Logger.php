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

class Logger
{
    private static $instance = null;
    private static $prefix = [];

    private $filename;

    protected function __construct()
    {
        $this->filename = self::buildFileName('log_' . date('Y-m-d_H-i-s'));
    }

    public static function buildFileName($basename)
    {
        \common\helpers\Assert::stringMatched($basename, '/^[-\w]+$/', 'incorrect log');
        return dirname(__DIR__).'/logs/' . $basename . '.log';
    }

    protected function __clone() { }

    public static function get()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function log($msg)
    {
        if (false === @file_put_contents($this->filename, self::getPrefix() . "$msg\n", FILE_APPEND)) {
            \Yii::warning('Could write to log: ' , $this->filename);
        }

    }

    public function log_record($record, $msg)
    {
        $this->log('Row ' . \OscLink\Helper::getIdentAR($record) . ': ' . $msg);
    }

    public static function print($msg)
    {
        self::get()->log( $msg );
    }

    public static function printf()
    {
        self::print( call_user_func_array('sprintf', func_get_args()) );
    }


    public function getFilename()
    {
        return $this->filename;
    }

    public static function getPrefix()
    {
        $prefix = implode(': ', self::$prefix);
        return empty($prefix)? '' : $prefix.'=> ';
    }

    public static function addPrefix($prefix)
    {
        self::$prefix[] = $prefix;
    }

    public static function clearPrefix()
    {
        \common\helpers\Assert::assert(count(self::$prefix) > 0);
        array_pop(self::$prefix);
    }
}