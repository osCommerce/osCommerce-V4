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
 * Price for all conditions
 */

namespace common\models\Product;

use Yii;
use common\classes\platform;
use common\helpers\Tax;
use common\helpers\Product as ProductHelper;
use common\helpers\Inventory as InventoryHelper;
use common\helpers\Customer;
use common\models\Products;
use common\models\ProductsPrices;

/**
 * update instance details if new params are passed
 */
class Price extends BasePrice {

    private static $iList = [];
    //protected static $stateKeys = ['curr_id', 'group_id', 'qty', 'type', 'customer_id', 'parent'];
    //protected static $priceKeys = ['stateParams', 'qty', 'type', 'inventory_special_price', 'inventory_price', 'special_price', 'products_price'];

    /*
     * [uprid] => [
     *          'products_price' => [
     *               'value' => 0,
     *               'vars' => [] - settings for calculation
     *           ],
     *          'special_price' => [
     *               'value' => 0
     *               'vars' => [] - settings for calculation
     *           ],
     * sometimes we need price for different q-ty on the same page
     *           'calculatedPrices => [
     * <products_price, special_price, inventory_price, inventory_special_price>
     *            => [
     *                 'value' => 0
     *                 'vars' => [] - settings for calculation
     *               ],
     *           ]
     *        ]
     */

    protected function __construct($uprid, $params=[]) {
        $this->origin_uprid = $uprid;
        $this->uprid = \common\helpers\Product::priceProductId($uprid);
        $this->calculate_full_price = false;
        $this->type = 'unit';
        $this->calculatedPrices = [];
        $this->qty = (isset($params['qty']) ? $params['qty'] : 0);
        $this->setParams($params, false);
        //$this->vids = []; //options
        $this->appendDisablingSettings();
        $this->inventory_price = [
            'value' => null,
          ];
        $this->products_price = [
            'value' => null,
            'vars' => [],
//                'type' => 'unit',
        ];
        self::$iList = [];
    }

    /**
     * save  current instance in cache and load required or empty
     * @param array $params
     */
    private function loadInstance($params) {
      //save current state
      $key = md5($this->uprid . json_encode($this->stateParams));
      if (!isset(self::$iList[$key])) {
        $tmp = clone $this;
        unset($tmp->calculatedPrices);
        self::$iList[$key] = $tmp;
      }

      //find saved or create new
      $key = md5($this->uprid . json_encode($params));
      if (isset(self::$iList[$key])) {
        $tmp = self::$iList[$key];
      } else {
        $tmp = new self($this->uprid, $params);
      }
      $this->restoreState($tmp);
    }

    /**
     * get calculated price
     * @param string $which
     * @param array $params
     * @return array|bool true - found, false not found
     */
    private function getCalculated($which, $params) {
      $ret = false;
      if (isset($this->calculatedPrices[$which]) && is_array($this->calculatedPrices[$which]) ) {
        foreach ($this->calculatedPrices[$which] as $price) {
          if (isset($price['vars']) && $price['vars'] == $params) {
            $ret = $price;
            break;
          }
        }
      }
      return $ret;
    }
  
/**
 * filter params (generally price depends on q-ty, group id, currency id) Other params could be ignored.
 * If the params are not changed calculated early prices are OK
 * @param array $params
 * @return $this
 */
    public function sanitizeParams(&$params) {
      foreach (array_keys($params) as $k) {
        if (!in_array($k, self::$stateKeys)) {
          unset($params[$k]);
        }
      }
      if (!isset($params['curr_id'])) {
          $params['curr_id'] = 0;
      }
      if (!isset($params['group_id'])) {
          $params['group_id'] = 0;
      }
      if (empty($params['qty'])) {
          $params['qty'] = 1;
      }
      //skip pack packaging if only units are passed
      if (is_array($params['qty']) ) {
          if (array_keys($params['qty']) == ['unit']) {
            $params['qty'] = $params['qty']['unit'];
          } elseif(!isset($params['type'])) {
              $params['type'] = array_keys($params['qty'])[0];
          }
      }

      foreach (self::$stateKeys as $k) {
        if (!isset($params[$k]) && isset($this->$k)) {
          $params[$k] = $this->$k;
        }
      }
      return $this;
    }

    /**
     * sanitize setter
     * @param array $params
     * @param bool  $onlySanitize 
     * @return $this
     */
    protected function setParams(&$params, $onlySanitize = true) {
      $this->sanitizeParams($params);
      if (!$onlySanitize) {
        $this->stateParams = $params;
      }
      return $this;
    }

    /**
     * restore price properties from cache
     * @param self $i
     */
    public function restoreState($i) {
      foreach (self::$priceKeys as $k) {
        if (!empty($i->$k)) {
          $this->$k = $i->$k;
        } else {
          $this->$k = null;
        }
      }
    }

    /**
     * checks whether the requested calc. params changed or not
     * @param array $params state keys params
     * @return false|this clone of this or false
     */
    public function checkState($params) {
      if (!empty($params)) {
        foreach (array_keys($params) as $k) {
          if (!in_array($k, self::$stateKeys)) {
            unset($params[$k]);
          }
        }
      }
      return (empty($params) || $this->stateParams == $params)?false : clone $this;
    }


    /**
     * ready product price
     * @param $params $qty = 1, $curr_id = 0, $group_id = 0 $customers_id = 0
     */
    public function getProductPrice($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = $this->getCalculated('products_price', $params);
        if (!$ret) {
          //switch state
          $this->loadInstance($params);
          //$this->setParams($params, false);
          $ret = parent::getProductPrice($params);
          $this->restoreState($saveInstance);
        } else {
          $ret = $ret['value'];
        }
      } else {
        $ret = parent::getProductPrice($params);
      }
      //echo "#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this, true) . "</PRE>";
      

      return $ret;
    }

    /**
     * ready special price
     */
    public function getProductSpecialPrice($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = $this->getCalculated('special_price', $params);
        if (!$ret) {
          //switch state
          $this->loadInstance($params);
          //$this->setParams($params, false);
          $ret = parent::getProductSpecialPrice($params);
          $this->restoreState($saveInstance);
        } else {
          $ret = $ret['value'];
        }
      } else {
        $ret = parent::getProductSpecialPrice($params);
      }
      return $ret;
    }

    /**
     * getter for special price details
     * special should be calculated before call of this function
     * Details is the same for both inventory and products special prices.
     */
    public function getSpecialPriceDetails($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = $this->getCalculated('special_price', $params);
        if (!$ret) {
          $ret = null;
        } else {
          // ret is correct array $ret ;
        }
      } else {
        $ret = parent::getSpecialPriceDetails($params);
      }
      return $ret;
    }

    /**
     *  q-ty discount price (only in helper for EP)
     */
    public function getProductsDiscountPrice($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = false;
      } else {
        $ret = parent::getProductsDiscountPrice($params);
      }
      return $ret;
    }

    

    public function getInventoryPrice($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = $this->getCalculated('inventory_price', $params);
        if (!$ret) {
          //switch state
          $this->loadInstance($params);
          //$this->setParams($params, false);
          $ret = parent::getInventoryPrice($params);
          $this->restoreState($saveInstance);
        } else {
          $ret = $ret['value'];
        }
      } else {
        $ret = parent::getInventoryPrice($params);
      }
      return $ret;
    }

    public function getInventorySpecialPrice($params) {
      $this->sanitizeParams($params);
      $saveInstance = $this->checkState($params);
      if ($saveInstance) {
        $ret = $this->getCalculated('inventory_special_price', $params);
        if (!$ret) {
          //switch state
          $this->loadInstance($params);
          //$this->setParams($params, false);
          $ret = parent::getInventorySpecialPrice($params);
          $this->restoreState($saveInstance);
        } else {
          $ret = $ret['value'];
        }
      } else {
        $ret = parent::getInventorySpecialPrice($params);
      }

      return $ret;

    }
    
    /**
     * @param $uprid
     * @return self
     */
    public static function getInstance($uprid) {
        if (!isset(self::$instanses[$uprid]) || (Yii::$app->params['reset_static_product_prices_cache'] ?? false)) {
            self::$instanses[$uprid] = new self($uprid);
        }
        return self::$instanses[$uprid];
    }
}
