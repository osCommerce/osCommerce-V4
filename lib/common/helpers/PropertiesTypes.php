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

use Yii;

class PropertiesTypes {

    public static function getTypes($mode = 'all') {

        \common\helpers\Translation::init('admin/properties');

        if ($mode == 'search') {
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
            ];
        } else if ($mode == 'filter') {
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
                'file' => TEXT_PR_FILE
            ];
        } else {//all
            return [
                'text' => TEXT_TEXT,
                'number' => TEXT_NUMBER,
                'interval' => TEXT_NUMBER_INTERVAL,
                'flag' => TEXT_PR_FLAG,
                'file' => TEXT_PR_FILE
            ];
        }
    }

}
