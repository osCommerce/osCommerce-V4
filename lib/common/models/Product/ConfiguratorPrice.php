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
use common\helpers\Tax;
use common\helpers\Product;
use common\classes\platform;
use common\helpers\Customer;
use common\helpers\Inventory as InventoryHelper;

class ConfiguratorPrice extends Price {

    use \common\helpers\SqlTrait;

    protected static $instanses = [];
    private static $vars = [];
    private $likeAndru = true;/* if true set calculation as Andrew version */

    /*
     * [urpid] => [
     *          'products_price_configurator' => [
     *               'value' => 0,
     *               'vars' => [] - settings for caluclation
     *           ],
     *        ]
     */

    private function __construct() {
        
    }

    /**
     * @param $uprid
     * @return self
     */
    public static function getInstance($uprid) {
        if (!isset(self::$instanses[$uprid]) || (Yii::$app->params['reset_static_product_prices_cache'] ?? false)) {
            self::$instanses[$uprid] = new self();
            self::$instanses[$uprid]->uprid = $uprid;
            self::$instanses[$uprid]->products_price_configurator = [
                'value' => null,
                'vars' => []
            ];
            $_uprid = \common\helpers\Inventory::normalize_id($uprid);
            self::$instanses[$uprid]->relative = parent::getInstance($_uprid);
        }        
        return self::$instanses[$uprid];
    }


    public function getConfiguratorPrice($params){
        
        $this->setParams($params);
        
        if (isset($this->products_price_configurator['vars']) && $this->products_price_configurator['vars'] == $params) {
            return $this->products_price_configurator['value'];
        } else {
            $this->products_price_configurator['vars'] = $params;
        }
        
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $_currency_id = (isset($params['curr_id']) && $params['curr_id'] ? $params['curr_id'] : \Yii::$app->settings->get('currency_id'));
        $_customer_groups_id = (isset($params['group_id']) && $params['group_id'] ? $params['group_id'] : $customer_groups_id);
        
        if ($_customer_groups_id != 0 && !Customer::check_customer_groups($_customer_groups_id, 'groups_is_show_price')) {
            return false;
        }

        if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $query = tep_db_query("select products_price_configurator  from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $this->uprid . "' and groups_id = '" . (int) $_customer_groups_id . "' and currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $_currency_id : '0') . "'");
            $data = tep_db_fetch_array($query);
            if (!$data || ($data['products_price_configurator'] == -2) || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select products_price_configurator from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $this->uprid . "' and groups_id = '0' and currencies_id = '" . $_currency_id . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_price_configurator from " . TABLE_PRODUCTS . " where products_id = '" . (int) $this->uprid . "'"));
                }
                if ($this->relative->dSettings->applyGroupDiscount()){
                    $discount = Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
                    $data['products_price_configurator'] = ($data['products_price_configurator']??0) * (1 - ($discount / 100));
                    if ($ext = \common\helpers\Acl::checkExtension('UserGroupsExtraDiscounts', 'getProductDiscount')) {
                        $product_discount = $ext::getProductDiscount($_customer_groups_id, $this->uprid);
                        $data['products_price_configurator'] = $data['products_price_configurator'] * (1 - ($product_discount / 100));
                    }
                }
            }
        } else {
            $data = tep_db_fetch_array(tep_db_query("select products_price_configurator from " . TABLE_PRODUCTS . " where products_id = '" . (int) $this->uprid . "'"));
        }
                       
        $baseInventoryPrice = (float)$this->relative->getInventoryPrice($params);
        $this->calculate_full_price = $this->relative->calculate_full_price;
        if ((float) $data['products_price_configurator'] == 0) {
            $data['products_price_configurator'] = $baseInventoryPrice;
        } else {
            if ($this->calculate_full_price){
                if ($this->likeAndru){
                    $data['products_price_configurator'] = $baseInventoryPrice/$data['products_price_configurator'];
                } else {
                    $data['products_price_configurator'] = $baseInventoryPrice;
                }
            } else {                
                $basePrice = $this->relative->getProductPrice($params);
                if (!$basePrice) $basePrice = 1;
                if ($this->likeAndru){
                    $baseDiff = $baseInventoryPrice / $basePrice;
                    if (!$baseDiff) $baseDiff = 1;
                    $data['products_price_configurator'] *= $baseDiff;
                } else {
                    $data['products_price_configurator'] += $baseInventoryPrice - $basePrice;
                }
            }
        }

        $this->products_price_configurator['value'] = $data['products_price_configurator'];

        $this->applyConfiguratorDiscount();
        
        return $this->products_price_configurator['value'];
    }
    
    public function applyConfiguratorDiscount(){
        if ($this->relative->dSettings->applyConfiguratorDiscount()){
            $check = tep_db_fetch_array(tep_db_query("select products_discount_configurator from " . TABLE_PRODUCTS . " where products_id = '" . (int) $this->uprid . "'"));
            if ($check && $this->products_price_configurator['value']){
                $this->products_price_configurator['value'] *= (1 - ($check['products_discount_configurator'] / 100));
            }
        }
    }
    
    public function getConfiguratorSpecialPrice($params){
        //2do
        return false;
    }


    /**
     * 2do getter for inventory special price details (value  and id)
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
}
