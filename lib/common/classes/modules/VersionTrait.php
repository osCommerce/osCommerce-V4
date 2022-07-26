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

/**
 * Used by Module and Setup
 */
trait VersionTrait {

    public static function getVersionHistory() {
//        return [
//            '1.1.0' => [
//                  'whats_new' =>
//                          "added Import All button\n" .
//                          "fixed Removing order mapping is not saved\n",
//                  // migration section contains migtation files to upgrade/downgrade to/from version
//                  'migration' => ['path_to_migration_file1', 'path_to_migration_file2']
//            ],
//            '1.0.0' => 'Initial release'];
    }

    /* Don't override if getVersionHistory is implemented */
    public static function getVersion()
    {
        $default = '0.0.1';
        $arr = static::getVersionHistory();
        if (!empty($arr)) {
            if (!function_exists('array_key_first')) { // PHP < 7.3
                foreach($arr as $key => $unused) {
                    return \common\classes\modules\ModuleVer::parseCommonFormat($key, $default);
                }
            } else {
                return array_key_first($arr);
            }
        }
        return \common\classes\modules\ModuleVer::parseCommonFormat($default);
    }

}
