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
use common\models\queries\OrdersSplintersQuery;
/**
 * This is the model class for table "orders_splinters".
 *
 * @property integer $splinters_id
 * @property integer $orders_id
 * @property integer $splinters_status
 * @property text $splinters_order - contain data of owner changes
 * @property integer $splinters_type - owner type of changing
 * @property integer $admin_id
 * @property datetime $date_added
 * @property integer $qty
 * @property float $value_exc_vat
 * @property float $value_inc_tax
 * @property string $splinters_owner - owner identifier
 * @property integer $splinters_suborder_id - INV OR CN numbers
 */
class OrdersSplinters extends ActiveRecord
{    
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'orders_splinters';
    }
   
    public function rules() {
        return [
            [['splinters_status', 'splinters_type'], 'required'],
            [['orders_id', 'splinters_suborder_id', 'splinters_order', 'admin_id', 'qty', 'value_exc_vat', 'value_inc_tax', 'splinters_owner'], 'safe'],
        ];
    }
    
    public static function primaryKey() {
        return ['splinters_id'];
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
    
    public function getAdmin(){
        return $this->hasOne(Admin::className(), ['admin_id' => 'admin_id']);
    }
    
    public static function create($order_id, $status, $type, $qty, $exc_price, $inc_price, $owner_uid, $data= null, $admin_id = null){
                
        $splinter = new self();
        $splinter->orders_id = $order_id;
        $splinter->splinters_status = $status;
        $splinter->splinters_type = $type;
        $splinter->splinters_owner = $owner_uid;
        $splinter->qty = $qty;
        $splinter->value_exc_vat = $exc_price;
        $splinter->value_inc_tax = $inc_price;
        $splinter->value_inc_tax = $inc_price;
        $splinter->splinters_order = $data;
        
        if ($splinter->validate()){
            $splinter->save();
        }
        return $splinter;
    }
    
    public static function find() {
        return new OrdersSplintersQuery(get_called_class());
    }
    
    public static function getOrdersSplinters($order_id){
        return self::find()->where(['orders_id' => $order_id])
                    ->orderBy('date_added');
    }

}
