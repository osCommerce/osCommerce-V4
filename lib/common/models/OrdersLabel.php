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

use frontend\design\Info;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "orders_label".
 *
 * @property integer $orders_label_id
 * @property integer $orders_id
 * @property string $label_class
 * @property integer $tracking_numbers_id
 * @property string $tracking_number
 * @property string $parcel_label_pdf
 * @property integer $admin_id
 * @property string $date_created
 * @property integer $label_status
 * @property string $label_module_error
 *
 */
class OrdersLabel extends ActiveRecord {

    const LABEL_STATUS_ERROR = -1;
    const LABEL_STATUS_ASYNC_READY = 2;
    const LABEL_STATUS_DONE = 1;

    /**
     * set table name
     * @return string
     */
    public static function tableName() {
        return 'orders_label';
    }

    public function beforeDelete() {
        if ($this->orders_id && $this->orders_label_id) {
            Yii::$app->db->createCommand()->delete(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS, ['orders_id' => $this->orders_id, 'orders_label_id' => $this->orders_label_id])->execute();
        }
        return parent::beforeDelete();
    }

    public function getOrdersLabelProducts() {
        $selected_order_products = [];
        if ($this->orders_id && $this->orders_label_id) {
            foreach ((new \yii\db\Query())->select('orders_products_id, products_quantity')->from(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS)->where(['orders_id' => $this->orders_id, 'orders_label_id' => $this->orders_label_id])->all() as $selected_products) {
                $selected_order_products[$selected_products['orders_products_id']] = $selected_products['products_quantity'];
            }
        }
        return $selected_order_products;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)){
            return false;
        }
        if ( $insert ){
            if ( is_null($this->admin_id) && Info::isTotallyAdmin() && isset($_SESSION['login_id']) ){
                $this->admin_id = (int)$_SESSION['login_id'];
            }
            if ( empty($this->date_created) ) {
                $this->date_created = new Expression('NOW()');
            }
        }
        return true;
    }


}
