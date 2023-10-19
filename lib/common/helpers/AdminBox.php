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

class AdminBox
{

    public static function buildNavigation($lastElement = '')
    {
        $path = [];
        $queryResponse = \common\models\AdminBoxes::findOne(['title' => $lastElement]); 
        if (is_object($queryResponse)) {
            $box_id = $queryResponse->box_id;
            do {
                $queryResponse = \common\models\AdminBoxes::findOne(['box_id' => $box_id]); 
                if (is_object($queryResponse)) {
                    $path[] = $queryResponse->title;
                    $box_id = $queryResponse->parent_id;
                } else {
                    $box_id = 0;
                }
            } while ($box_id > 0);
            
            
        }
        $path = array_reverse($path);
        return $path;
    }

    public static function getData($id = '')
    {
        return \common\models\Admin::find()->select([
            'firstname' => 'admin_firstname',
            'lastname' => 'admin_lastname',
            'emai' => 'admin_email_address',
            'avatar' => 'avatar',
        ])->where(['admin_id' => (int)$id])->asArray()->one();

    }
}
