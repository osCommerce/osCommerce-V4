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


class ApiHelper
{

    static public function generateApiKey()
    {
        $__server_part = tep_db_fetch_array(tep_db_query(
            "SELECT UUID() AS server_part"
        ));
        return strtolower(str_replace('-','',$__server_part['server_part']).\common\helpers\Password::create_random_value(16));
    }

}