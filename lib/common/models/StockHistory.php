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

/**
 * This is the model class for table "stock_history".
 *
 * @property int $warehouse_id
 * @property string $products_id
 * @property int $prid
 * @property string $products_model
 * @property int $products_quantity_before
 * @property int $warehouse_quantity_before 
 * @property string $products_quantity_update_prefix
 * @property int $products_quantity_update
 * @property string $comments
 * @property int $orders_id
 * @property int $admin_id
 * @property int $is_temporary
 */

class StockHistory extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'stock_history';
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
    
    /*params = array*/
    public function saveHistory($params){
        if (is_array($params)){
            foreach($params as $name => $value){
                if ($this->hasAttribute($name)){
                    $this->{$name} = $value;
                }
            }
        }
        
        return $this->save(false);
    }
}