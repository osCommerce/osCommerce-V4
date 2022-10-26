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

use \common\classes\modules\ModuleExtensions;
use \common\helpers\Acl;

class Extensions
{

    private static $cacheAllowed = [];

    static function isAllowed(string $code)
    {

        if (!isset(self::$cacheAllowed[$code])) {
            self::$cacheAllowed[$code] = Acl::checkExtensionAllowed($code);
        }
        return self::$cacheAllowed[$code];
    }

    static function isCustomerGroupsAllowed()
    {
        return self::isAllowed('UserGroups');
    }

}