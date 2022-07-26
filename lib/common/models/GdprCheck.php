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

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class GdprCheck extends ActiveRecord {

    public static function tableName() {
        return 'gdpr_check';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_send'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

}
