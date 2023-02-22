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

namespace common\classes\modules;

class ModuleVer {

    public $major = 0;
    public $minor = 0;
    public $build = 0;

    private const REG_FORMAT_FILE   = '/\d{1,3}-\d{1,2}-\d{1,2}/';
    private const REG_FORMAT_COMMON = '/\d{1,3}.\d{1,2}.\d{1,2}/';

    public function __construct($major = 0, $minor = 0, $build = 0)
    {
        $this->major = (int) $major;
        $this->minor = (int) $minor;
        $this->build = (int) $build;
    }

    private static function badFormat($version, $default, $expected)
    {
        if (is_null($default)) {
            throw new \Exception( sprintf('Module version is incorrect (expected %s): %s', $expected, var_export($version, true)) );
        }
        return self::parse($default, '0.0.0');
    }

    public static function parse($version, $default = null)
    {
        if ($version instanceof self) {
            return $version;
        } elseif (is_numeric($version)) {
            return self::parseNumber($version, $default);
        } elseif(is_string($version)) {
            if (preg_match(self::REG_FORMAT_FILE, $version)) {
                return self::parseFileFormat($version, $default);
            } elseif (preg_match(self::REG_FORMAT_COMMON, $version)) {
                return self::parseCommonFormat($version, $default);
            }
        }
        return self::badFormat($version, $default, 'mixed');
    }

    public static function parseNumber($version, $default = null)
    {
        if (!is_numeric($version)) {
            return self::badFormat($version, $default, 'number');
        }
        $major = floor($version);
        $version -= $major;
        $version *= 100;
        $minor = floor($version);
        $version -= $minor;
        $version *= 100;
        return new self($major, $minor, $version);
    }

    public static function parseFileFormat($version, $default = null)
    {
        if (!is_string($version) || !preg_match(self::REG_FORMAT_FILE, $version, $match)) {
            return self::badFormat($version, $default, 'file format');
        }
        list($major, $minor, $build) = explode('-', $match[0]);
        return new self($major, $minor, $build);
    }

    public static function parseCommonFormat($version, $default = null)
    {
        if (!is_string($version) || !preg_match(self::REG_FORMAT_COMMON, $version, $match)) {
            return self::badFormat($version, $default, 'common format');
        }
        list($major, $minor, $build) = explode('.', $match[0]);
        return new self($major, $minor, $build);
    }

    public function toNumber()
    {
        return $this->major + $this->minor/100 + $this->build/10000;
    }

    public function toFileFormat()
    {
        return sprintf("%u_%u_%u", $this->major, $this->minor, $this->build);
    }

    public function toCommonFormat()
    {
        return sprintf("%u.%u.%u", $this->major, $this->minor, $this->build);
    }

    public function toCompareFormat()
    {
        return sprintf("%u.%02u.%02u", $this->major, $this->minor, $this->build);
    }

    public function compareTo($ver)
    {
        return self::compare($this, $ver);
    }

    public static function compare($ver1, $ver2)
    {
        $ver1 = self::parse($ver1);
        $ver2 = self::parse($ver2);
        return version_compare($ver1->toCompareFormat(), $ver2->toCompareFormat());
    }
}
