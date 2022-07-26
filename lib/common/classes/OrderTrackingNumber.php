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


use common\models\TrackingCarriers;
use common\models\TrackingNumbers;
use common\models\TrackingNumbersToOrdersProducts;

class OrderTrackingNumber extends TrackingNumbers
{

    public $tracking_url;
    public $number;
    public $carrier;
    public $products = [];
    public $products_quantity = 0;

    protected $modified_products = false;

    public function getproducts_quantity()
    {
        return array_sum($this->products);
    }

    public static function instanceFromString($tracking, $orderId)
    {
        $obj = new self();
        $obj->orders_id = $orderId;
        $obj->tracking_number = trim($tracking);

        $obj->dataLoaded();

        return $obj;
    }

    public function dataLoaded()
    {
        $parsed = \common\helpers\Order::parse_tracking_number($this->tracking_number);
        $this->number = $parsed['number'];
        $this->tracking_url = $parsed['url'];
        if ( !empty($parsed['carrier']) ) {
            $this->carrier = $parsed['carrier'];
            $this->tracking_carriers_id = \common\helpers\OrderTrackingNumber::getCarrierId($this->carrier);
        }
        if ( empty($this->tracking_url) ){
            if ($this->tracking_carriers_id && $carrier = TrackingCarriers::findOne(['tracking_carriers_id'=>$this->tracking_carriers_id])){
                $this->carrier = $carrier->tracking_carriers_name;
                $this->tracking_url = $carrier->tracking_carriers_url.$this->number;
            }else {
                $this->carrier = '';
                $this->tracking_url = TRACKING_NUMBER_URL.str_replace(' ','', $this->number);
            }
        }
    }

    public static function getTrackingFromTable($orderId)
    {
        $tracking_table = static::find()
            ->where(['orders_id'=>$orderId])
            ->orderBy(['tracking_numbers_id'=>SORT_ASC])
            ->all();
        foreach ($tracking_table as $tracking){
            /**
             * @var $tracking OrderTrackingNumber
             */
            $tracking->dataLoaded();

            foreach (
                TrackingNumbersToOrdersProducts::find()
                    ->where(['tracking_numbers_id'=>$tracking->tracking_numbers_id])
                    ->andWhere(['orders_id'=>$tracking->orders_id])
                    ->orderBy(['orders_products_id'=>SORT_ASC])
                    ->all() as $orderProductTrack){
                $tracking->products[$orderProductTrack->orders_products_id] = $orderProductTrack->products_quantity;
            }

        }
        return $tracking_table;
    }

    public function isProductsModified()
    {
        return $this->modified_products;
    }

    public function setOrderProducts($products)
    {
        if ( is_array($products) ) {
            $this->products = $products;
            $this->modified_products = true; //TODO: need data check for modified array
        }
    }

    public function saveProducts()
    {
        $process_products = $this->products;
        $currentCollection = TrackingNumbersToOrdersProducts::find()
            ->where(['tracking_numbers_id'=>$this->tracking_numbers_id])
            ->andWhere(['orders_id'=>$this->orders_id])
            ->all();
        foreach ($currentCollection as $currentProduct) {
            if (isset($process_products[$currentProduct->orders_products_id])) {
                $qtyDelta = ((int)$process_products[$currentProduct->orders_products_id] - (int)$currentProduct->products_quantity);
                if ($qtyDelta > 0) {
                    if (\common\helpers\OrderProduct::doDispatchSpecific($currentProduct->orders_products_id, $qtyDelta) == true AND $qtyDelta > 0) {
                        $currentProduct->products_quantity += $qtyDelta;
                        try {
                            $currentProduct->save();
                        } catch (\Exception $exc) {}
                    }
                }
                unset($process_products[$currentProduct->orders_products_id]);
            }
        }
        foreach ($process_products as $orders_products_id=>$products_quantity){
            $obj = new TrackingNumbersToOrdersProducts();
            $obj->tracking_numbers_id = $this->tracking_numbers_id;
            $obj->orders_id = $this->orders_id;
            $obj->orders_products_id = $orders_products_id;
            if (\common\helpers\OrderProduct::doDispatchSpecific($orders_products_id, $products_quantity) == true AND $products_quantity > 0) {
                $obj->products_quantity = $products_quantity;
                try {
                    $obj->save(false);
                } catch (\Exception $exc) {}
            }
        }
        \common\helpers\Order::evaluate($this->orders_id);
        $this->modified_products = false;
    }

    public function afterRefresh()
    {
        parent::afterRefresh();
        $this->dataLoaded();
    }


    public function __toString()
    {
        if ( !empty($this->carrier) ) {
            return (string)$this->carrier.','.(string)$this->number;
        }
        return (string)$this->number;
    }


}