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

namespace common\services;

use common\helpers\PriceFormula;
use Yii;
use common\models\Suppliers;

#[\AllowDynamicProperties]
class SupplierService implements \IteratorAggregate {
    /* change @var $status */

    public $allow_change_status = false;

    /* change @var $is_default */
    public $allow_change_default = false;

    /* change @var $suppliers_surcharge_amount */
    public $allow_change_surcharge = false;

    /* change @var $suppliers_margin_percentage */
    public $allow_change_margin = false;

    /* change @var $price_formula */
    public $allow_change_price_formula = false;

    /* change $auth */
    public $allow_change_auth = false;
    
    public $currencies_editor_simple = false;
    public $currencies;
    public $currenciesMap;

    public function __construct() {
        $this->currencies = Yii::$container->get('currencies');
        $this->currenciesMap = \yii\helpers\ArrayHelper::map($this->currencies->currencies, 'id', 'title');
    }

    public $supplier;
    public $delivery_indicators;

    public function loadSupplier($suppliers_id) {
        if ($suppliers_id) {
            $this->supplier = Suppliers::findOne(['suppliers_id' => $suppliers_id]);
        }

        if (!$this->supplier) {
            $this->supplier = new Suppliers();
            $this->supplier->country = 'GB';
            $this->supplier->invoice_needed_to_complete_po = 1;
        }

        $this->supplier->getAuthData()->one();
        $this->supplier->getSupplierCurrencies()->all();

        if (!$this->supplier->currencies_id) {
            $this->supplier->currencies_id = $this->_currencies->currencies[DEFAULT_CURRENCY]['id'] ?? null;
        }

        $this->defineDeliveryInfo();

        $this->definePriceFormula();
    }

    public function defineDeliveryInfo() {
        $this->delivery_indicators = \common\classes\StockIndication::get_delivery_terms();
        if ($this->delivery_indicators) {
            $this->delivery_indicators = \yii\helpers\ArrayHelper::map($this->delivery_indicators, 'id', 'text');
        }
    }

    public $price_formula_text;

    public function definePriceFormula() {
        $this->price_formula_text = '';
        if ( $_formula = PriceFormula::getSupplierFormula($this->supplier->suppliers_id) ) {
            if ( is_array($_formula) && !empty($_formula['text']) ) {
                $this->price_formula_text = $_formula['text'];
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function getIterator() {
        return new \ArrayIterator($this);
    }

    public function get($object, $as = null) {
        $_proto = $this->getIterator();
        if (isset($_proto[$object])) {
            return $_proto[$object];
        } else {
            $newObject = Yii::createObject($object);
            if (!is_null($as)){
                $this->{$as} = $newObject;
                return $this->{$as};
            } else {
                $this->{$object} = $newObject;
                return $this->{$object};
            }
        }
    }
    
    public function set($property, $value){
        $_proto = $this->getIterator();
        if (isset($_proto[$property])) {
            $_proto[$property] = $value;
            return $_proto[$property];
        } else {            
            throw new Exception('Property not found');
        }
    }


    public function getProducts(){
        if (!property_exists($this, 'products')){
            $this->products = $this->supplier->getSupplierProducts()->all();
        }
        
        return $this->products;
    }

    public function render($type, $params = []) {

        if (class_exists($type)) {
            $_ref = new \ReflectionClass($type);

            if ($_ref->getParentClass()->name == "yii\base\Widget") {
                return $type::widget(array_merge($params, ['service' => $this]));
            }
        }
    }

}
