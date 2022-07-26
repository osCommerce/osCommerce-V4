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

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class AdminMessages extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'admin_messages';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public static function getUnread(){
        return self::find()->where(['status' => 'unread']);
    }
}