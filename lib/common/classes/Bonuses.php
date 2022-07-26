<?php

namespace common\classes;

use Yii;
use common\classes\Order;
use common\classes\OpcOrder;
use common\classes\shopping_cart;
use yii\helpers\ArrayHelper;

class Bonuses {

    public $total_price;
    public $total_cost;
    public $products_bonus_list = [];
    public $products_bonus_earn_list = [];
    public $enabled;
    public $customer_bonus_points_earn;
    public $customer_bonus_points_redeem;
    public $customer_bonus_points_left;
    public $customer_bonus_points;
    public $instance;
    private $calculated = false;
    private $manager;

    public function __construct(\common\services\OrderManager $manager = null) {
        $this->resetBonuses();
        $this->manager = $manager;
        $this->enabled = ((MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS == 'true') ? true : false);
        $this->customer_bonus_points_earn = !$manager->isCustomerAssigned() ? 0: $manager->getCustomersIdentity()->customers_bonus_points;
        
        $this->customer_bonus_points = ($manager->has('customer_bonus_points') ? (float)$manager->get('customer_bonus_points') : $this->customer_bonus_points_earn);

        if (\frontend\design\Info::isTotallyAdmin()){
            global $cart;
            if (is_object($cart)){
                if ( ($bp = $cart->getTotalKey('ot_bonus_points')) !== false ){
                    global $order;
                    if ($order->order_id  > 0 ){
                        $this->customer_bonus_points_earn += $order->getRedeemAmount(true);
                        $this->customer_bonus_points = number_format($bp['in']);
                    }   
                }
            }
        }
    }

    public function getBonusesFormInstance($instance) {
        if ($this->enabled) {
            if ($instance instanceof Order || $instance instanceof OpcOrder) { // from order
                $this->instance = 'order';
                if (is_array($instance->products)) {
                    $products = ArrayHelper::index($instance->products, 'id');
                    $this->prepareBonuses($products);
                }
            } else if ($instance instanceof shopping_cart) { //from cart
                $this->instance = 'cart';
                $products_array = $instance->get_products();
                $products_array = ArrayHelper::index($products_array, 'id');
                if (is_array($products_array)) {
                    $this->prepareBonuses($products_array);
                }
            }
        }
        return $this;
    }

    public function prepareBonuses($products) {
        $this->resetBonuses();
        foreach ($products as $products_id => $product_values) {
            if ($product_values['bonus_points_price'] > 0) {
                $qty = $product_values['qty'] ? $product_values['qty'] : $product_values['quantity'];
                $points_price = $product_values['bonus_points_price'] * $qty;
                $points_cost = $product_values['bonus_points_cost'] * $qty;
                $this->total_price += $points_price;
                $this->total_cost += $points_cost;
                $this->products_bonus_list[$products_id] = [
                    'price' => $product_values['bonus_points_price'],
                    'cost' => $product_values['bonus_points_cost'],
                    'price_total' => $points_price,
                    'cost_total' => $points_cost,
                    'final_price' => (float) $product_values['final_price'] * $product_values['qty'],
                    'tax' => \common\helpers\Tax::calculate_tax(($product_values['final_price'] * $product_values['qty']), $product_values['tax']),
                    'tax_description' => $product_values['tax_description'],
                ];
            }
        }
        return $this;
    }

    public function setPointsToRedeem($amount) {
        $this->customer_bonus_points = (float) $amount;
    }
    
    public function hasBonuses(){
        return $this->customer_bonus_points_earn > 0;
    }

    public function calculateBonuses($bonus_apply) {
        
        if ($this->enabled) {
            if ($this->calculated) return;
            uasort($this->products_bonus_list, function($a, $b) {
                if ($a['price_total'] == $b['price_total'])
                    return 0;
                return ($a['price_total'] < $b['price_total']) ? -1 : 1;
            });

            $this->customer_bonus_points_left = (float)$this->customer_bonus_points;
            $redeem = 0;
            
            foreach ($this->products_bonus_list as $products_id => $product) {
                if ($this->customer_bonus_points_left <= 0 || !$bonus_apply) {
                    $this->products_bonus_earn_list[$products_id] = $this->products_bonus_list[$products_id];
                    unset($this->products_bonus_list[$products_id]);
                } else {
                    $this->clearProductCost($products_id);
                    if ($this->customer_bonus_points_left >= $product['price_total']) {
                        $this->products_bonus_list[$products_id]['redeem'] = $product['price_total'];
                        $this->products_bonus_list[$products_id]['redeem_partly'] = false;
                        $this->customer_bonus_points_left -= $product['price_total'];
                        $redeem += $product['price_total'];
                    } else {
                        $this->products_bonus_list[$products_id]['redeem'] = $this->customer_bonus_points_left;
                        $redeem += $this->customer_bonus_points_left;
                        $this->products_bonus_list[$products_id]['redeem_text'] = sprintf('will be redeemed %s only from %s', $this->products_bonus_list[$products_id]['redeem'], $this->products_bonus_list[$products_id]['price_total']);
                        $this->customer_bonus_points_left = 0;
                        $this->products_bonus_list[$products_id]['redeem_partly'] = true;
                        $k = $this->products_bonus_list[$products_id]['redeem'] / $this->products_bonus_list[$products_id]['price_total'];
                        $this->products_bonus_list[$products_id]['final_price'] = $this->products_bonus_list[$products_id]['final_price'] * $k;
                        $this->products_bonus_list[$products_id]['tax'] = $this->products_bonus_list[$products_id]['tax'] * $k;
                    }
                }
            }
            if ($redeem && $redeem < $this->customer_bonus_points) {
                $this->customer_bonus_points = $redeem;
            }
            $this->customer_bonus_points_left = $this->customer_bonus_points_earn - $this->customer_bonus_points;
            if (\frontend\design\Info::isTotallyAdmin()){
            }
            $this->calculated = true;
        }
    }
    
    public function clearProductCost($products_id){
        if ($this->instance == 'order' && is_object($this->manager)){
            $order = $this->manager->getOrderInstance();
            if (is_array($order->products)){
                foreach ($order->products as $key => $product){
                    if ($product['id'] == $products_id) {
                        $order->products[$key]['bonus_points_cost'] = 0;
                        return;
                    }
                }
            }
        }
        return;
    }

    public static function getDetails() {
        return null;
    }
    /*use over \common\service\OrderManager
     * public function getDetails() {
        global $order;
        if (isset($GLOBALS['ot_bonus_points']) && is_object($GLOBALS['ot_bonus_points']) && $GLOBALS['ot_bonus_points']->enabled) {
            if (is_object($GLOBALS['ot_bonus_points']->bonuses)) {
                $bonuses = $GLOBALS['ot_bonus_points']->bonuses;
            } else {
                $bonuses = (new self())->getBonusesFormInstance($order);
            }
            
            return [
                'bonuses' => $bonuses,
                'can_use_bonuses' => $bonuses->hasBonuses() && $bonuses->canUseBonuses(),
                'bonus_apply' => @$_SESSION['bonus_apply'],
            ];
        }
        return null;
    }*/

    public function getRedeemValue() {
        return $this->customer_bonus_points;
    }

    public function getLeftValue() {
        return (float) $this->customer_bonus_points_left;
    }

    public function resetBonuses() {
        $this->total_price = 0;
        $this->total_cost = 0;
    }

    public function getBonusesPrice() {
        return $this->total_price;
    }

    public function getBonusesCost() {
        if (count($this->products_bonus_earn_list)) {
            return array_sum(ArrayHelper::getColumn($this->products_bonus_earn_list, 'cost_total'));
        }
        return 0;
    }

    public function canUseBonuses() {
        if (!count($this->products_bonus_list) && !count($this->products_bonus_earn_list))
            return false;
        return true;
    }

    public function getBonusList() {
        return $this->products_bonus_list;
    }

    public static function updateCustomerBonuses($data) {
        
        if (!$data['customers_id']) {
            $data['customers_id'] = Yii::$app->user->getId();
        }
        if (!in_array($data['prefix'], ['+', '-'])) {
            $data['prefix'] = '+';
        }

        if ($data['customers_id']) {
            if ($data['credit_amount'] != 0){
                $customer = new \common\components\Customer();
                $customer = $customer->updateBonusPoints($data['customers_id'], (float)$data['credit_amount'], $data['prefix']);
                if (! $customer->isGuest()) {
                    $customer->saveCreditHistory($data['customers_id'], $data['credit_amount'], $data['prefix'], '', 1, $data['comments'], 1, $data['customer_notified'] ?? 0);
                }
            }
        }
    }
    
    public function updateAmount($data){
        if ($data['customers_id'] && in_array($data['prefix'], ['+', '-'])) {
            $customer = new \common\components\Customer();
            $customer->updateBonusPoints($data['customers_id'], (float)$data['credit_amount'], $data['prefix']);
        }
    }
    
    public static function setAppliedBonuses($order_id){
        if ($order_id){
            tep_db_query("update " . TABLE_ORDERS . " set bonus_applied = 1 where orders_id = '" . (int)$order_id . "'");
        }
    }
    
    public function setUnAppliedBonuses($order_id){
        if ($order_id){
            tep_db_query("update " . TABLE_ORDERS . " set bonus_applied = 0 where orders_id = '" . (int)$order_id . "'");
        }
    }

}
