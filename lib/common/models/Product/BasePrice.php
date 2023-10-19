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
use yii\helpers\ArrayHelper;
use common\classes\platform;
use common\helpers\Tax;
use common\helpers\Product as ProductHelper;
use common\helpers\Inventory as InventoryHelper;
use common\helpers\Customer;
use common\models\Products;
use common\models\ProductsPrices;

#[\AllowDynamicProperties]
class BasePrice {

    //use \common\helpers\SqlTrait;

    protected static $instanses = [];
    //private static $vars = [];
    protected static $stateKeys = ['curr_id', 'group_id', 'qty', 'type', 'customer_id', 'parent'];
    protected static $priceKeys = ['stateParams', 'qty', 'type', 'inventory_special_price', 'inventory_price', 'special_price', 'products_price'];

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
     * sometimes we need price for different q-ty on th same page
     *           'calculatedPrices => [
     * products_price, special_price, inventory_price, inventory_special_price
     *            => [
     *                 'value' => 0
     *                 'vars' => [] - settings for calculation
     *               ],
     *           ],
     *            stateParams = > [$stateKeys]
     *        ]
     */

    /**
     * check price in calculated array and then either return it or calculate it first.
     * @param string $which
     * @param array $params
     * @return bool true - found, false not found
     */
    protected function returnCalculated($which, $params) {
      $vals = $this->checkCalculated($which, $params);
      if (!$vals){
        // not yet calculated 
        $ret = $this->$which($params);
      } else {
        $ret = $this->$which['value'];
      }
      return $ret;
    }

    /**
     * check prices in calculated array and set this->$which if found
     * @param string $which
     * @param array $params
     * @return bool true - found, false not found
     */
    protected function checkCalculated($which, $params) {
      $ret = false;
      if (isset($this->calculatedPrices[$which]) && is_array($this->calculatedPrices[$which]) ) {
        foreach ($this->calculatedPrices[$which] as $price) {
          if (isset($price['vars']) && $price['vars'] == $params) {
            $this->$which = $price;
            $ret = true;
            break;
          }
        }
      }
      return $ret;
    }
  
    /**
     * @param string $which
     */
    protected function saveCalculated($which) {
      if (!isset($this->calculatedPrices[$which]) ) {
        $this->calculatedPrices[$which] = [];
      }
      if ($which != 'inventory_price' || !empty($this->$which['calculated'])) {
        $this->calculatedPrices[$which][] = $this->$which;
      }
    }

    /**
     * check params and params used for calculation
     * @param string $which
     * @param array $params
     * @return bool 
     */
    private function isChanged($which, $params) {
      $ret = true;
      if (isset($this->$which['vars']) && $this->$which['vars'] == $params ) {
        $ret = false;
      }
      return $ret;
    }
   
    public function appendDisablingSettings(){
        $this->dSettings = new \common\components\DisabledSettings($this->uprid); // now only 1 flag on product - disable all discounts
    }
    
/**
 * filter params (generally price depends on q-ty, group id, currency id) Other params could be ignored.
 * If the params are not changed calculated early prices are OK
 * @param array $params
 * @return $this
 * /
    public function setParams(&$params) {
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
      return $this;
    }*/

    /**
     * to deprecate - this->qty is updated by several methods (getPrice, getSpecialPrice, getSpecialInventoryPrice) and they all call each other
     * So it's impossible to determine whether q-ty changed or not
     * @param int|array $qty
     * @param bool $specials
     * @return boolean
     */
    public function isChangedQtyType($qty) {
        $changed = false;
        if (is_array($qty)) {
            foreach ($qty as $key => $value) {
                if ($this->type != $key) {
                    $changed = true;
                    $this->type = $key;
                    //$this->products_price['type'] = $key;
                }
                //$qty = $value;
                break;
            }
        } else {
            $this->type = 'unit';
            if ($this->qty != $qty){
                $changed = true;
                $this->qty = $qty;
            }
        }
        return $changed;
    }

    /**
     * ready product price
    $products_id, $qty = 1, $price = 0, $curr_id = 0, $group_id = 0 $customers_id = 0
     */
    public function getProductPrice($params) {
        $this->setParams($params);
        $qty = $params['qty'];

        $isChanged = $this->isChangedQtyType($qty);

        if ((!is_null($this->products_price['value']) && !$this->isChanged('products_price', $params) && !$isChanged) || $this->checkCalculated('products_price', $params) ) {
            return $this->products_price['value'];
        } else {
            $this->products_price['value'] = null;
            $this->products_price['vars'] = $params;
        }

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $_currency_id = (int) (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (int) (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
              $this->products_price['value'] = false;
              $this->saveCalculated('products_price');
              return false;
            }
        }

        $tmp = null;
        $productItem = Yii::$container->get('products')->getProduct((int) $this->uprid);
        if ($productItem) {
            $tmp = $productItem->getArrayCopy();
        }else{
            $productItem = new \common\components\ProductItem(['products_id'=>$this->uprid]);
        }

        $qFields = ['products_price', 'products_price_full', 'pack_unit', 'products_price_pack_unit', 'packaging', 'products_price_packaging'];
        if ($tmp && 
            count(array_diff_key( array_flip($qFields), $tmp)) == 0 // all keys exists
            ) {
          $product = $tmp;

        } else {

          $product = Products::find()->select($qFields)
                        ->where('products_id=:products_id', [':products_id' => (int) $this->uprid])->asArray()->one();
          if (is_array($tmp)) {
            Yii::$container->get('products')->loadProducts($product);
          }
        }
        if (is_array($product) && array_key_exists('_p_products_price',$product) ){
            $product['products_price'] = $product['_p_products_price'];
        }

        $this->calculate_full_price = (bool) ($product['products_price_full'] ?? null);
        $this->products_price['value'] = $product['products_price'] ?? null;

        $CustomerProductsPricePossible = false;
        /* @var $CustomerProducts \common\extensions\CustomerProducts\CustomerProducts */
        if ($CustomerProducts = \common\helpers\Acl::checkExtension('CustomerProducts', 'allowed')) {
          if ($CustomerProducts::allowed() && !Yii::$app->user->isGuest){
            $CustomerProductsPricePossible = true;
          }
        }

        if (!\common\helpers\Acl::checkExtensionAllowed('ProductBundles') && USE_MARKET_PRICES != 'True' && !\common\helpers\Extensions::isCustomerGroupsAllowed()
            && $this->products_price['value'] > 0 && $qty == 1 && !$CustomerProductsPricePossible) {
            $this->saveCalculated('products_price');
            return $this->products_price['value'];
        }

        $data = null;
        /* @var $CustomerProducts \common\extensions\CustomerProducts\CustomerProducts */
        if ($CustomerProductsPricePossible) {
          $customer_id = (int) \Yii::$app->user->getId();
          $_customer_id = (int) (isset($params['customer_id']) && $params['customer_id'] ? $params['customer_id'] : $customer_id);
          $data = $CustomerProducts::getPrice((int) $this->uprid, (int)$_customer_id, (USE_MARKET_PRICES == 'True' ? $_currency_id : '0'));
        }

        $apply_discount = false;
        if (!$data) {
          if (USE_MARKET_PRICES == 'True' || (\common\helpers\Extensions::isCustomerGroupsAllowed() && (int)$_customer_groups_id>0) ){
              $data = $productItem->getProductsPrices([':products_id' => (int) $this->uprid, ':groups_id' => (int) $_customer_groups_id, ':currencies_id' => (USE_MARKET_PRICES == 'True' ? $_currency_id : '0')]);
              if (!$data || ($data['products_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                  if (USE_MARKET_PRICES == 'True') {
                      $data = $productItem->getProductsPrices([':products_id' => (int) $this->uprid, ':groups_id' => 0, ':currencies_id' => $_currency_id]);
                  } else {
                      $data = $product;
                  }
                  $apply_discount = true;
              }
          } else {
              $data = $product;
          }
        }

        $this->products_price['value'] = $data['products_price'] ?? null;

        if ($this->type != 'unit') {
            $this->applyPacks($data['products_price_pack_unit'] ?? null, $data['products_price_packaging'] ?? null);
        }

        if ($apply_discount) {
            $this->applyGroupDiscount($_customer_groups_id);
        }

        if ($qty > 1) {
            $this->getProductsDiscountPrice(['qty' => $qty]);
        }

        foreach (\common\helpers\Hooks::getList('base-price/get-product-price') as $filename) {
            include($filename);
        }

        $this->saveCalculated('products_price');
        return $this->products_price['value'];
    }

    private function getPackInfo() {
        return Products::find()->select('pack_unit, packaging')
                        ->where('products_id =:products_id', [':products_id' => (int) $this->uprid])->one();
    }

/**
 * shit... updates $this->products_price and $this->inventory_price
 * @param float $products_price_pack_unit
 * @param float $products_price_packaging
 * @param bool $toBase
 */
    public function applyPacks($products_price_pack_unit, $products_price_packaging, $toBase = true) {
        $pack_info = $this->getPackInfo();

        if ($toBase) {
            switch ($this->type) {
                case 'packaging':
                    if ($products_price_packaging > 0) {
                        $this->products_price['value'] = (float) $products_price_packaging;
                    } elseif ($pack_info['pack_unit'] > 0 && $pack_info['packaging'] > 0) {
                        $this->products_price['value'] *= $pack_info['pack_unit'] * $pack_info['packaging'];
                    }
                    break;
                case 'pack_unit':
                    if ($products_price_pack_unit > 0) {
                        $this->products_price['value'] = (float) $products_price_pack_unit;
                    } elseif ($pack_info['pack_unit'] > 0) {
                        $this->products_price['value'] *= $pack_info['pack_unit'];
                    }
                    break;
                case 'unit':
                default :
                    break;
            }
        } else {
            if ($this->calculate_full_price || true) {
                switch ($this->type) {
                    case 'packaging':
                        if ($pack_info['pack_unit'] > 0 && $pack_info['packaging'] > 0) {
                            $this->inventory_price['value'] *= $pack_info['pack_unit'] * $pack_info['packaging'];
                        }
                        break;
                    case 'pack_unit':
                        if ($pack_info['pack_unit'] > 0) {
                            $this->inventory_price['value'] *= $pack_info['pack_unit'];
                        }
                        break;
                    case 'unit':
                    default :
                        break;
                }
            }
        }
    }

    public function applyGroupDiscount($_customer_groups_id) {
        if ($_customer_groups_id && $this->dSettings->applyGroupDiscount()) {
            $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
            $this->products_price['value'] = $this->products_price['value'] * (1 - ($discount / 100));
            if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                $this->products_price['value'] = $this->products_price['value'] * (1 - ($product_discount / 100));
            }
        }
    }

    /** 2do - make it protected params: $qty, $products_price, $curr_id = 0, $group_id = 0 */
    protected function getProductsDiscountPrice($params) {
        //[$type => $qty], $data['products_price']

        $this->setParams($params);

        if (is_null($this->products_price['value'])) {
            $tmp = $params;
            $tmp['qty'] = 1; // 2do check min q-ty to purchase :(
            $this->getProductPrice($tmp);
        }

        $qty = $params['qty'];
        $type = 'unit';
        if (is_array($qty)) {
            $qty = current($params['qty']);
            $type = key($params['qty']);
        }

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $_currency_id = (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);

        if ($this->dSettings->applyQtyDiscount()){
            $apply_discount = false;
            if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                $data = ProductsPrices::find()->select(['products_group_discount_price as products_price_discount', 'products_group_price',
                                    'products_group_discount_price_pack_unit as products_price_discount_pack_unit', 'products_group_price_pack_unit',
                                    'products_group_discount_price_packaging as products_price_discount_packaging', 'products_group_price_packaging'])
                                ->where('products_id = :products_id and groups_id = :groups_id and currencies_id = :currencies_id', [
                                    ':products_id' => (int) $this->uprid,
                                    ':groups_id' => (int) $_customer_groups_id,
                                    ':currencies_id' => (USE_MARKET_PRICES == 'True' ? $_currency_id : '0')
                                ])->asArray()->one();
                if (!$data || ($data['products_price_discount'] == '' && $data['products_group_price'] == -2) || $data['products_price_discount'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                    if (USE_MARKET_PRICES == 'True') {
                        $data = ProductsPrices::find()->select(['products_group_discount_price as products_price_discount',
                                            'products_group_discount_price_pack_unit as products_price_discount_pack_unit',
                                            'products_group_discount_price_packaging as products_price_discount_packaging'])
                                        ->where('products_id =:products_id and groups_id = 0 and currencies_id = :currencies_id', [
                                            ':products_id' => (int) $this->uprid,
                                            ':currencies_id' => (int) $_currency_id
                                        ])->asArray()->one();
                    } else {
                        $data = Products::find()->select('products_price_discount, products_price_discount_pack_unit, products_price_discount_packaging')
                                        ->where('products_id = :products_id', [':products_id' => (int) $this->uprid])->asArray()->one();
                    }
                    $data['products_price_discount'] = $data['products_price_discount'] ?? null;
                    $apply_discount = true;
                }
            } else {
                $data = Products::find()->select('products_price_discount, products_price_discount_pack_unit, products_price_discount_packaging')
                                ->where('products_id = :products_id', [':products_id' => (int) $this->uprid])->asArray()->one();
            }

            switch ($type) {
                case 'packaging':
                    $data['products_price_discount'] = $data['products_price_discount_packaging'];
                    break;
                case 'pack_unit':
                    $data['products_price_discount'] = $data['products_price_discount_pack_unit'];
                    break;
                case 'unit':
                default :
                    break;
            }


            $data['products_price_discount'] = trim($data['products_price_discount'], '; ');

            if ($data['products_price_discount'] == '' || $data['products_price_discount'] == -1 || $data['products_price_discount'] == -2) {
                return $this->products_price['value'];
            }
            $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['products_price_discount'])); // remove final separator

            if (!is_array($ar) || count($ar)<2 || count($ar)%2==1) { // incorrect table format - skip
              return $this->products_price['value'];
            }

            for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                if ($qty < (int)$ar[$i]) {
                    if ($i == 0) {
                        return $this->products_price['value'];
                    }
                    $price = $ar[$i - 1];
                    break;
                }
            }
            if (isset($ar[$i - 2])  && (int)$ar[$i - 2]>0 && $qty >= (int)$ar[$i - 2]) {
                $this->products_price['value'] = $ar[$i - 1];
            }

            if ($apply_discount) {
                $this->applyGroupDiscount($_customer_groups_id);
            }
        }
        
        return $this->products_price['value'];
    }

    /**
     * ready special price
     */

    public function getProductSpecialPrice($params) {

        $this->setParams($params);

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $_currency_id = (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);

        $qty = $params['qty'];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkShowPrice($customer_groups_id)) {
                return false;
            }
        }

        if (method_exists(get_called_class(), 'check_product')) {
            if (!ProductHelper::check_product($this->uprid, 1, true)) {
                return false;
            }
        }

        if (!isset($this->special_price['value'])) {
            $this->special_price = [
                'value' => null,
                'vars' => []
            ];
        }

        $isChanged = $this->isChangedQtyType($qty);
        if ((!is_null($this->special_price['value']) && !$this->isChanged('special_price', $params) && !$isChanged) || $this->checkCalculated('special_price', $params) ) {
          return $this->special_price['value'];
          
        } else {
          $this->special_price['value'] = false;
          $this->special_price['vars'] = $params;
        }

        if ($this->isChanged('products_price', $params) && !$this->checkCalculated('products_price', $params)) {
          $this->getProductPrice($params);
        }

        $apply_discount = true;

        if ($apply_discount) {
            if ($this->dSettings->applySale()) {
                $special_date_columns = ", s.expires_date AS special_expiration_date, promote_type ";
                $special_date_columns .= ", s.max_per_order, s.total_qty ";
                $special_date_columns .= ", IF(s.date_status_change IS NULL,IFNULL(s.specials_last_modified,specials_date_added), GREATEST(s.date_status_change,IFNULL(s.specials_last_modified,s.specials_date_added)) ) AS special_start_date ";
                if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    if (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0) {
                       $specials_query = tep_db_query("select s.specials_id, s.specials_new_products_price {$special_date_columns} from " . TABLE_SPECIALS . " s where s.products_id = '" . (int) $this->uprid . "' and s.status=1 and specials_new_products_price>0 and not exists (select * from " . TABLE_SPECIALS_PRICES . " sp where s.specials_id = sp.specials_id and sp.groups_id=0 and sp.specials_new_products_price=-1) order by specials_new_products_price>0 desc, s.specials_new_products_price");
                    } else {
                        $specials_query = tep_db_query("select s.specials_id, if(sp.specials_new_products_price is NULL, -2, sp.specials_new_products_price) as specials_new_products_price {$special_date_columns} from " . TABLE_SPECIALS . " s left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int) $_customer_groups_id . "'  and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $_currency_id : '0') . "' where s.products_id = '" . (int) $this->uprid . "'  and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.status  order by if(sp.specials_new_products_price is NULL, -2, sp.specials_new_products_price)>0 desc, if(sp.specials_new_products_price is NULL, -2, sp.specials_new_products_price)");
                    }
                } else {
                    $specials_query = tep_db_query("select s.specials_id, s.specials_new_products_price {$special_date_columns} from " . TABLE_SPECIALS . " s where s.products_id = '" . (int) $this->uprid . "' and s.status=1 order by s.specials_new_products_price");
                }

                $special_start_date = false;
                $special_expiration_date = false;
                $promote_type = false;
                if (tep_db_num_rows($specials_query)) {
                  while ($special = tep_db_fetch_array($specials_query) ) { //remove limit in queries
                    //$special = tep_db_fetch_array($specials_query);
                    
                    if (!is_array($special)) {
                      $this->special_price['value'] = false;
                      $this->saveCalculated('special_price');
                      return $this->special_price['value'];
                    }

                    if (is_array($qty)) {
                      // Fix Error: Unsupported operand types
                      $qty = current($qty);
                    }
                    if (!empty($special['total_qty']) && \common\helpers\Specials::checkSoldOut($special, $qty)) {
                      continue; //remove limit
                      $this->special_price['value'] = false;
                      $this->saveCalculated('special_price');
                      return $this->special_price['value'];
                    }

                    if (!empty($special['max_per_order']) && $special['max_per_order']<$qty) {
                      continue; //remove limit
                      $this->special_price['value'] = false;
                      $this->saveCalculated('special_price');
                      return $this->special_price['value'];
                    }
                    break; //remove limit
                  } //remove limit

                  \common\helpers\Php8::nullProps($special, ['special_start_date', 'special_expiration_date', 'promote_type', 'specials_id', 'specials_new_products_price']);
                  $special_start_date = $special['special_start_date'];
                    $special_expiration_date = $special['special_expiration_date'];
                    if ($special['promote_type']>0) {
                      $promote_type = $special['promote_type'];
                    } elseif ($special['promote_type']==0 && defined('SALES_DEFAULT_PROMO_TYPE') && SALES_DEFAULT_PROMO_TYPE != 'None'){
                        switch (SALES_DEFAULT_PROMO_TYPE) {
                          case 'Percent':
                            $promote_type = 1;
                            break;
                          case 'Fixed':
                            $promote_type = 2;
                            break;
                        }
                    }
                    $this->special_price['specials_id'] = $special['specials_id'];
                    $this->special_price['value'] = $special['specials_new_products_price'];
                    if ($this->special_price['value'] == -2) {
                        //group discount on "main" special price (this Id, ignore all possible other)
                        if ($_customer_groups_id != 0) {
                            if (USE_MARKET_PRICES == 'True') {
                                $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . (int) $special['specials_id'] . "' and currencies_id = '" . (int) $_currency_id . "' and groups_id = 0 and specials_new_products_price>0 order by specials_new_products_price desc limit 1");
                            } else {
                                $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " s where specials_id = '" . (int) $special['specials_id'] . "' and products_id = '" . (int) $this->uprid . "' and status and specials_new_products_price>0 order by specials_new_products_price limit 1");
                            }
                            if (tep_db_num_rows($specials_query)) {
                                $special = tep_db_fetch_array($specials_query);
                                $special['specials_new_products_price'] = $special['specials_new_products_price'] ?? null;
                                if (Customer::check_customer_groups($_customer_groups_id, 'apply_groups_discount_to_specials')) {
                                    $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                                    $this->special_price['value'] = $special['specials_new_products_price'] * (1 - ($discount / 100));
                                    if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                                        $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                                        $this->special_price['value'] = $this->special_price['value'] * (1 - ($product_discount / 100));
                                    }
                                } else {
                                    $this->special_price['value'] = $special['specials_new_products_price'];
                                }
                            } else {
                                $this->special_price['value'] = false;
                            }
                        } else {
                            $this->special_price['value'] = false;
                        }
                    }
                } else {
                    $this->special_price['value'] = false;
                }

                if ($this->type != 'unit') {
                    $p1 = $p2 = null;
                    $pack_info = $this->getPackInfo();
                    if ($this->type == 'pack_unit') {
                        $this->special_price['value'] *= $pack_info['pack_unit'];
                    } else {
                        $this->special_price['value'] *= $pack_info['pack_unit'] * $pack_info['packaging'];
                    }
                }

                if ($this->special_price['value'] <= 0) {
                    $this->special_price['value'] = false;
                }
                if ($this->special_price['value'] >= $this->products_price['value']) {
                    $this->special_price['value'] = false;
                }
                if ($this->special_price['value'] !== false) {
                    $this->special_price = array_merge([
                      'special_start_date' => $special_start_date,
                      'special_expiration_date' => $special_expiration_date,
                      'special_promote_type' => $promote_type,
                      'special_total_qty' => $special['total_qty']??0,
                      'special_max_per_order' => $special['max_per_order']??0,
                      'value_no_promo' => $this->special_price['value']
                      ], $this->special_price);
                    $this->attachToProduct([
                      'promo_class' => 'sale', 
                      'special_start_date' => $special_start_date, 
                      'special_expiration_date' => $special_expiration_date,
                      'special_promote_type' => $promote_type,
                      'special_total_qty' => $special['total_qty']??0,
                      'special_max_per_order' => $special['max_per_order']??0,
                    ]);
                }
            } else {
                $this->special_price['value'] = false;
            }
        }

        foreach (\common\helpers\Hooks::getList('base-price/get-product-special-price') as $filename) {
            include($filename);
        }

        $this->saveCalculated('special_price');
        return $this->special_price['value'];
    }

    public function updateUprid($new) {
        $this->origin_uprid = $new;
        $this->uprid = \common\helpers\Product::priceProductId($new);
        $this->tax_class_id = null;
        return $this;
    }

    public function getTaxClassId()
    {
        if ( !isset($this->tax_class_id) || is_null($this->tax_class_id) ) {
          $vids = [];
          $virtual_vids = [];
            $_uprid = InventoryHelper::normalizeInventoryPriceId($this->uprid, $vids, $virtual_vids);
            $_get_class_r = tep_db_query(
                "SELECT IFNULL(i.inventory_tax_class_id,p.products_tax_class_id) AS tax_class_id " .
                "FROM " . TABLE_PRODUCTS . " p " .
                " LEFT JOIN " . TABLE_INVENTORY . " i ON p.products_id=i.prid AND i.products_id='".tep_db_input($_uprid)."' " .
                "WHERE p.products_id='".(int)$_uprid."'"
            );
            if ( tep_db_num_rows($_get_class_r)>0 ) {
                $_get_class = tep_db_fetch_array($_get_class_r);
                $this->tax_class_id = $_get_class['tax_class_id'];
            }
        }
        return $this->tax_class_id;
    }

    public function getInventoryPrice($params) {

        $this->setParams($params);
        
        if (!isset($this->inventory_price['value'])) {
            $this->inventory_price = [
                'value' => false,
                'vars' => [],
                'calculated' => false
            ];
        }

        $qty = $params['qty'];

        if (!$this->isChanged('inventory_price', $params) || $this->checkCalculated('inventory_price', $params)) {
            return $this->inventory_price['value'];
        } else {
            $this->inventory_price['calculated'] = false;
            $this->inventory_price['value'] = false;
            $this->inventory_price['vars'] = $params;
        }

        if ($this->isChanged('products_price', $params) && !$this->checkCalculated('products_price', $params)) {
          $this->getProductPrice($params);
        }

        if (strpos($this->uprid, '%') !== false) {
            $this->inventory_price['value'] = $this->products_price['value'];
            $this->saveCalculated('inventory_price');
            return $this->inventory_price['value'];
        }
      
        $vids = $virtual_vids = $__vids = [];
        $_uprid = InventoryHelper::normalizeInventoryPriceId($this->uprid, $vids, $virtual_vids);
        InventoryHelper::normalizeInventoryPriceId($this->origin_uprid, $__vids, $virtual_vids);

        if (\common\helpers\Extensions::isAllowed('Inventory') && strpos($this->uprid,'{')!==false && !InventoryHelper::disabledOnProduct($_uprid)) {

            $check_inventory = tep_db_fetch_array(tep_db_query("select inventory_id, if(price_prefix = '-', -inventory_price, inventory_price) as inventory_price, inventory_full_price as inventory_full_price from " . TABLE_INVENTORY . " i where products_id = '" . tep_db_input($_uprid) . "' and non_existent = '0' " . InventoryHelper::get_sql_inventory_restrictions(array('i', 'ip')) . " limit 1"));
            if ($check_inventory) {
                if (!$this->calculate_full_price) {
                    $this->inventory_price['value'] = $check_inventory['inventory_price'];
                    $inventory_price = $this->getInventoryGroupPrice($params);
                    if ($inventory_price != -1) {
                        $this->inventory_price['value'] = (float) $this->products_price['value'] + $inventory_price;
                    }
                } else {
                    $this->inventory_price['value'] = $check_inventory['inventory_full_price'];
                    $inventory_price = $this->getInventoryGroupPrice($params);
                    if ($inventory_price != -1) {
                        $this->inventory_price['value'] = (float) $inventory_price;
                    }
                }
            } else {
                $this->inventory_price['value'] = $this->products_price['value'];
            }
            if ($virtual_vids) {
                $this->inventory_price['value'] += \common\helpers\Attributes::get_virtual_attribute_price($this->origin_uprid, $virtual_vids, $params['qty'], $this->inventory_price['value']);
            }
        } else {
            $this->inventory_price['value'] = $this->products_price['value'];
            if ($vids) {
                $attributes_price_percents = [];
                $attributes_price_percents_base = [];
                $attributes_price_fixed = 0;
                foreach ($vids as $options_id => $value) {
                    $option_arr = explode('-', $options_id);
                    $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) (ArrayHelper::getValue($option_arr, 1) > 0 ? $option_arr[1] : $_uprid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                    $attribute_price = tep_db_fetch_array($attribute_price_query);
                    $attribute_price['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attribute_price['products_attributes_id'] ?? null, $params['qty'] ?? null);

                    $attribute_price['price_prefix'] = $attribute_price['price_prefix'] ?? null;
                    if ( $attribute_price['price_prefix']=='-%' ) {
                        $attributes_price_percents[] = 1-$attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '+%'){
                        $attributes_price_percents[] = 1+$attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '+%b'){
                        $attributes_price_percents_base[] = $attribute_price['options_values_price']/100;
                    }elseif($attribute_price['price_prefix'] == '-%b'){
                        $attributes_price_percents_base[] = -1*$attribute_price['options_values_price']/100;
                    }else{
                        $attributes_price_fixed += (($attribute_price['price_prefix']=='-')?-1:1)*$attribute_price['options_values_price'];
                    }
                }
                $tmp = $this->inventory_price['value'] += $attributes_price_fixed;
                foreach( $attributes_price_percents_base as $attributes_price_percent ) {
                    $this->inventory_price['value'] += $tmp*$attributes_price_percent;
                }
                foreach( $attributes_price_percents as $attributes_price_percent ) {
                    $this->inventory_price['value'] *= $attributes_price_percent;
                }
            }
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
            $bundle_products = $ext::getBundleProducts(\common\helpers\Inventory::get_prid($this->uprid), \common\classes\platform::currentId());
            if (count($bundle_products) > 0) {
                $check = Products::find()->select('use_sets_discount, products_sets_discount, products_sets_price_formula')->where('products_id=:products_id', [':products_id' => (int)\common\helpers\Inventory::get_prid($this->uprid)])->asArray()->one();
// {{
                $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
                $_currency_id = (int) (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
                $_customer_groups_id = (int) (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);
                $check_group = \common\models\ProductsPrices::find()->select('products_sets_discount')->where('products_id=:products_id and groups_id=:groups_id and currencies_id=:currencies_id', [':products_id' => (int)\common\helpers\Inventory::get_prid($this->uprid), ':groups_id' => $_customer_groups_id, ':currencies_id' => (USE_MARKET_PRICES == 'True' ? $_currency_id : '0')])->asArray()->one();
// }}
                if ($check['use_sets_discount'] && $this->dSettings->applyBundleDiscount()) {
                    if (!empty($check['products_sets_price_formula'])) {
                        $products_sets_price_formula = json_decode($check['products_sets_price_formula'], true);
                        if (is_array($products_sets_price_formula) && isset($products_sets_price_formula['formula'])) {
                            $this->inventory_price['value'] = \common\helpers\PriceFormula::apply(
                                            $products_sets_price_formula, [
                                        'price' => floatval($this->inventory_price['value']),
                                        'discount' => floatval($check['products_sets_discount'] + $check_group['products_sets_discount']),
                                        'margin' => 0,
                                        'surcharge' => 0,
                            ]);
                        }
                    } elseif (($check['products_sets_discount'] + $check_group['products_sets_discount']) > 0) {
                        $this->inventory_price['value'] *= (1 - (($check['products_sets_discount'] + $check_group['products_sets_discount']) / 100));
                    }
                }
            }
        }

        $this->inventory_price['calculated'] = true;
        $this->saveCalculated('inventory_price');
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($this, true) ."</PRE>"; 
        return (float) $this->inventory_price['value'];
    }

    /*
     * used for final inventory price
     */

    private function getInventoryGroupPrice($params) {

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $_currency_id = (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }

        if (!\common\helpers\Acl::checkExtensionAllowed('ProductBundles') && USE_MARKET_PRICES != 'True' && !\common\helpers\Extensions::isCustomerGroupsAllowed() && $params['qty'] == 1) {
            return $this->inventory_price['value'];
        }
                
        $discount = 0;
        if (!$this->calculate_full_price) {
            if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                $query = tep_db_query("select inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "' order by inventory_price asc limit 1");
                $data = tep_db_fetch_array($query);
                if (!$data || ($data['inventory_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                    if (USE_MARKET_PRICES == 'True') {
                        $data = tep_db_fetch_array(tep_db_query("select inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "' order by inventory_price asc limit 1"));
                    } else {
                        $data['inventory_price'] = $this->inventory_price['value'];
                    }
                    if ($this->dSettings->applyGroupDiscount()){
                        $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                        $this->inventory_price['value'] = $data['inventory_price'] * (1 - ($discount / 100));
                        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                            $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                            $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($product_discount / 100));
                        }
                    }
                } else {
                    $this->inventory_price['value'] = $data['inventory_price'];
                }
            }
        } else { //do for full price        
            if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                $query = tep_db_query("select inventory_full_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($this->uprid) . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "' order by inventory_full_price asc limit 1");
                $data = tep_db_fetch_array($query);
                if (!$data || ($data['inventory_full_price'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                    if (USE_MARKET_PRICES == 'True') {
                        $data = tep_db_fetch_array(tep_db_query("select inventory_full_price from " . TABLE_INVENTORY_PRICES . " where products_id like '" . tep_db_input($this->uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "' order by inventory_full_price asc limit 1"));
                    } else {
                        $data['inventory_full_price'] = $this->inventory_price['value']; //tep_db_fetch_array(tep_db_query("select inventory_full_price from " . TABLE_INVENTORY . " where products_id like '" . tep_db_input($this->uprid) . "' order by inventory_full_price asc limit 1"));
                    }
                    if ($this->dSettings->applyGroupDiscount()){
                        $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                        $this->inventory_price['value'] = $data['inventory_full_price'] * (1 - ($discount / 100));
                        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                            $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                            $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($product_discount / 100));
                        }
                    }
                } else {
                    $this->inventory_price['value'] = $data['inventory_full_price'];
                }
            }
        }

        if ($this->type != 'unit') {
            $this->applyPacks(0, 0, false);
        }


        if (((is_array($params['qty']) && array_sum($params['qty']) > 1) || (!is_array($params['qty']) && $params['qty'] > 1)) && $this->inventory_price['value'] > 0) {
            $this->inventory_price['value'] = $this->getInventoryDiscountPrice($params);
        }

        foreach (\common\helpers\Hooks::getList('base-price/get-inventory-group-price') as $filename) {
            include($filename);
        }

        $this->saveCalculated('inventory_price');
        return $this->inventory_price['value'];
    }

    protected function getInventoryDiscountPrice($params) {

        //not changed - nothing to validate $this->setParams($params);

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        $_currency_id = (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);

        $qty = (is_array($params['qty']) ? $params['qty'][$this->type] : $params['qty']);

        if ($this->dSettings->applyQtyDiscount()){
            if (!$this->calculate_full_price) {
                $apply_discount = false;
                if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    $query = tep_db_query("select inventory_group_discount_price as inventory_discount_price, inventory_group_price as inventory_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "'");
                    $data = tep_db_fetch_array($query);
                    if (!$data || ($data['inventory_discount_price'] == '' && $data['inventory_price'] == -2) || $data['inventory_discount_price'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                        if (USE_MARKET_PRICES == 'True') {
                            $data = tep_db_fetch_array(tep_db_query("select inventory_group_discount_price as inventory_discount_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "'"));
                        } else {
                            $data = tep_db_fetch_array(tep_db_query("select inventory_discount_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($this->uprid) . "'"));
                        }
                        $apply_discount = true;
                    }
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_discount_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($this->uprid) . "'"));
                }

                if ($data['inventory_discount_price'] == '') {
                    return $this->inventory_price['value'];
                }
                $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['inventory_discount_price'])); // remove final separator
                for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                    if ($qty < $ar[$i]) {
                        if ($i == 0) {
                            return $this->inventory_price['value'];
                        } else {
                            $this->inventory_price['value'] = $ar[$i - 1];
                            break;
                        }
                    }
                }
                if ($qty >= $ar[$i - 2]) {
                    $this->inventory_price['value'] = $ar[$i - 1];
                }
                if ($apply_discount && $this->dSettings->applyGroupDiscount()) {
                    $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                    $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($discount / 100));
                    if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                        $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                        $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($product_discount / 100));
                    }
                }
            } else { // full price
                $apply_discount = false;
                if ((USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())) {
                    $query = tep_db_query("select inventory_discount_full_price, inventory_full_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "'");
                    $data = tep_db_fetch_array($query);
                    if (!$data || ($data['inventory_discount_full_price'] == '' && $data['inventory_full_price'] == -2) || $data['inventory_discount_full_price'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                        if (USE_MARKET_PRICES == 'True') {
                            $data = tep_db_fetch_array(tep_db_query("select inventory_discount_full_price as inventory_discount_full_price from " . TABLE_INVENTORY_PRICES . " where products_id = '" . tep_db_input($this->uprid) . "' and groups_id = '0' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int) $_currency_id : 0) . "'"));
                        } else {
                            $data = tep_db_fetch_array(tep_db_query("select inventory_discount_full_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($this->uprid) . "'"));
                        }
                        $apply_discount = true;
                    }
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select inventory_discount_full_price from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($this->uprid) . "'"));
                }

                if ($data['inventory_discount_full_price'] == '') {
                    return $this->inventory_price['value'];
                }
                $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['inventory_discount_full_price'])); // remove final separator
                for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                    if ($qty < $ar[$i]) {
                        if ($i == 0) {
                            return $this->inventory_price['value'];
                        } else {
                            $this->inventory_price['value'] = $ar[$i - 1];
                            break;
                        }
                    }
                }
                if ($qty >= $ar[$i - 2]) {
                    $this->inventory_price['value'] = $ar[$i - 1];
                }
                if ($apply_discount && $this->dSettings->applyGroupDiscount()) {
                    $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                    $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($discount / 100));
                    if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                        $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                        $this->inventory_price['value'] = $this->inventory_price['value'] * (1 - ($product_discount / 100));
                    }
                }
            }
        }

        $this->saveCalculated('inventory_price');
        return $this->inventory_price['value'];
    }

    /**
     * getter for inventory special price details (value  and id)
     * @param array $params
     * @return array
     */
    public function getSpecialPriceDetails($params) {
      if (!is_null($this->special_price['value'])) {
        $ret = $this->special_price;
      } elseif (is_null($this->inventory_special_price['value'])) {
        $this->getInventorySpecialPrice($params);
        $ret = $this->inventory_special_price;
      }

      return $ret;
    }

    public function getInventorySpecialPrice($params) {

        $this->setParams($params);

        if (!isset($this->inventory_special_price['value']) || is_null($this->inventory_special_price['value'])) {
            $this->inventory_special_price = [
                'value' => false,
                'promote' => false,
                'vars' => []
            ];
        }

        $qty = $params['qty'];
        $isChanged = $this->isChangedQtyType($qty);

        if (!$isChanged && (!$this->isChanged('inventory_special_price', $params) || $this->checkCalculated('inventory_special_price', $params)) ) {
            if ($this->inventory_special_price['value']) {
                $this->attachToProduct(['promo_class' => 'sale']);
            }
            return $this->inventory_special_price['value'];
        } else {
          $this->inventory_special_price['value'] = false; // to avoid errors in a lot of if else below
          $this->inventory_special_price['vars'] = $params;
        }

        if ($this->isChanged('special_price', $params) && !$this->checkCalculated('special_price', $params) ) {
          $this->getProductSpecialPrice($params);
        }
        if ($this->isChanged('inventory_price', $params) && !$this->checkCalculated('inventory_price', $params) ) {
          $this->getInventoryPrice($params);
        }

        $apply_discount = true;
        $this->special_price['specials_id'] = $this->special_price['specials_id'] ?? null;
        if ($apply_discount) {
          if (strpos($this->origin_uprid, '{') && $this->dSettings->applySale()) { //2do specials for inventory
                if ($this->calculate_full_price) {
                    if ($this->special_price['value'] !== false) {
                        $this->inventory_special_price['value'] = $this->special_price['value'];
                        $this->inventory_special_price['specials_id'] = $this->special_price['specials_id'];
                    } else {
                        $this->inventory_special_price['value'] = false; //temporary while absent specials on inventory
                    }
                } else {
                    if ($this->special_price['value'] !== false) {
                        $this->inventory_special_price['value'] = $this->inventory_price['value'] - $this->products_price['value']; //price of attributes
                        $this->inventory_special_price['specials_id'] = $this->special_price['specials_id'];
                        // {{ PERCENT DISCOUNT TO ATTRIBUTES PRICE
                        if ( false && $this->products_price['value']!=0 ) {
                            $special_price_rate = $this->special_price['value'] * 100 / $this->products_price['value'] / 100;
                            $this->inventory_special_price['value'] *= $special_price_rate;
                        }
                        // }} PERCENT DISCOUNT TO ATTRIBUTES PRICE
                        //
                        $_special = $this->special_price['value'];
                        // TODO: extract to hook when above inventory hook will be extracted
                        if (\common\helpers\Acl::checkExtensionAllowed('Promotions') && $this->dSettings->applyPromotion()){
                          $promo = \common\extensions\Promotions\models\Product\PromotionPrice::getInstance($this->uprid);
                          $promoSettings = $promo->getSettings();
                          if ($promoSettings['to_both'] ) {
                            if ($this->special_price['value_no_promo']) {
                              $_special = $this->special_price['value_no_promo'];
                            } elseif ($this->inventory_price['value'])  {
                              $_special = $this->inventory_price['value'] - $this->inventory_special_price['value'];
                            }
                          }
                        }
                        /*
                         //special product price ($_special) already multiplied on pack/packaging
                        if ($this->type != 'unit') {
                            $pack_info = $this->getPackInfo();
                            if ($this->type == 'packaging') {
                                $_special *= $pack_info['pack_unit'] * $pack_info['packaging'];
                            } elseif ($this->type == 'pack_unit') {
                                $_special *= $pack_info['pack_unit'];
                            }
                        }
                         */
                        $this->inventory_special_price['value'] += $_special;

                        if ($this->inventory_special_price['value'] < 0) {
                            $this->inventory_special_price['value'] = false;
                        }
                    } else {
                      $this->inventory_special_price['value'] = false;
                    }
                }
                if ($this->inventory_special_price['value']) {
                    $this->attachToProduct(['promo_class' => 'sale']);
                }
            } else {
                $this->inventory_special_price['value'] = $this->special_price['value'];
                $this->inventory_special_price['specials_id'] = $this->special_price['specials_id'] ?? null;
            }
        }
        $this->inventory_special_price['value_no_promo'] = $this->inventory_special_price['value'];

        foreach (\common\helpers\Hooks::getList('base-price/get-inventory-special-price') as $filename) {
            include($filename);
        }

        $this->saveCalculated('inventory_special_price');
        return $this->inventory_special_price['value'];
    }

    public function attachToProduct($params) {
        $products = \Yii::$container->get('products');
        $product = $products->getProduct($this->uprid);
        if ($product) {
            $product->attachDetails($params);
        }
        return;
    }

}
