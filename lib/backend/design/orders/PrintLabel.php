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

namespace backend\design\orders;

use Yii;
use yii\base\Widget;

class PrintLabel extends Widget {

    public $order;
    public $manager;

    public function init() {
        parent::init();
    }

    public function run() {
        if (!empty($this->order->info['shipping_class'])) {
            list($module, $method) = explode('_', $this->order->info['shipping_class']);
            $shipping = $this->manager->getShippingCollection()->get($module);
            if ($this->order->canBeDelivered() && (is_object($shipping) && $shipping->hasLabelModule())) {
                $shipments_array = \common\models\OrdersLabel::find()
                                ->where(['orders_id' => $this->order->order_id])
                                ->asArray()->all();
                foreach ($shipments_array as $key => $shipment) {
                    list($label_module, $label_method) = explode('_', $shipment['label_class']);
                    if (!empty($label_module) && !empty($label_method)) {
                        $class = "common\\modules\\label\\" . $label_module;
                        if (class_exists($class) && is_subclass_of($class, "common\\classes\\modules\\ModuleLabel")) {
                            $shipments_array[$key]['class'] = new $class;
                        }
                    } else {
                        // if label_class is not selected yet - delete
                        \common\models\OrdersLabel::findOne(['orders_id' => $this->order->order_id, 'orders_label_id' => $shipment['orders_label_id']])->delete();
                    }
                }
                $products_left = array_sum(\yii\helpers\ArrayHelper::getColumn($this->order->products, 'qty'));
                $already_selected_products_query = (new \yii\db\Query())->select('products_quantity')->from(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS)->where(['orders_id' => $this->order->order_id])->all();
                foreach ($already_selected_products_query as $already_selected_products) {
                    $products_left -= $already_selected_products['products_quantity'];
                }
                return $this->render('print-label', [
                            'order' => $this->order,
                            'shipping' => $shipping,
                            'shipments_array' => $shipments_array,
                            'products_left' => $products_left,
                ]);
            }
        }
    }

}
