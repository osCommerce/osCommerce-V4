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


class Affiliate {

    public static function isLogged()
    {
        return tep_session_is_registered("login_affiliate") && \common\helpers\Acl::checkExtensionAllowed('Affiliate');
    }

    public static function id()
    {
        return (isset($_SESSION['affiliate_ref']) && \common\helpers\Acl::checkExtensionAllowed('Affiliate')) ? (int)$_SESSION['affiliate_ref'] : 0;
    }

    public static function where($aliasTable = '', $insertStrBefore = ' ')
    {
        if (!empty($alias)) $alias .= '.';
        return $insertStrBefore . $aliasTable . 'affiliate_id = ' . self::id();
    }

    public static function whereIfExists($aliasTable = '', $insertStrBefore = ' ')
    {
        return self::isLogged()? self::where($aliasTable, $insertStrBefore) : '';
    }

}
