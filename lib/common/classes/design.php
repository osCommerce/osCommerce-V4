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

namespace common\classes;

class design {

    public static function pageName($title){

        $page_name = strtolower($title);
        $page_name = str_replace(' ', '_', $page_name);
        return preg_replace('/[^a-z0-9_-]/', '', $page_name);
    }
}