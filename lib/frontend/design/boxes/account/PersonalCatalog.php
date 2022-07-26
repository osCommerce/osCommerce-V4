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

namespace frontend\design\boxes\account;

use common\models\Customers;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class PersonalCatalog extends Widget
{

    public $file;
    public $params;
    public $settings;
    /** @var PersonalCatalogService */
    private $personalCatalogService;
    /** @var int */
    private $languageId;
    /** @var bool|Customers */
    private $customer;

    /**
     * PersonalCatalog constructor.
     * @param array $config
     */
    public function __construct(
        array $config = []
    )
    {
        parent::__construct($config);
        try {
            $this->customer = \Yii::$app->user->isGuest ? false : \Yii::$app->user->getIdentity();
        } catch (\Throwable $t) {
            $this->customer = false;
        }
        $this->languageId = (int)\Yii::$app->settings->get('languages_id');
    }

    public function run(): string
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PersonalCatalog', 'allowed')) {
            $this->personalCatalogService = $ext::getService();
        } else {
            return '';
        }
        Info::addBlockToWidgetsList('cart-listing');
        Info::addBoxToCss('pagination');
        Info::addBoxToCss('quantity');

        $maxItems = $this->settings[0]['max_items'] ?? 3;
        if (
            !$this->personalCatalogService->isAllowed($this->customer) ||
            Info::isAdmin()
        ) {
            return '';
        }
        $get = \Yii::$app->request->get();
        $defWList = $this->personalCatalogService->getDefaultWishList($this->customer->customers_id);
        $allWlist = $this->personalCatalogService->getAllWishLists($this->customer->customers_id);
        
        $this->settings[0]['show_image'] = false;
        $this->settings['itemElements'] = ['image' => true];

        $persList = $this->personalCatalogService->getPersonalList($this->customer->customers_id);
        
        if ($get['show'] == 'wishlist'){
            $this->settings['listing_type'] = 'wishlist';
            if (isset($get['list']) && $get['list']){
                $list = $this->personalCatalogService->getListById(intval($get['list']), $this->customer->customers_id);
            } else {
                $list = $defWList;
                $defWList->className = 'active';
            }
        } else {
            $this->settings['listing_type'] = 'personal';
            $list = $persList;
            $persList->className = 'active';
        }
        //mark active and remove default whish lists in/from the list of all.
        foreach($allWlist as $k => $_list){
            $allWlist[$k]->list_id == $list->list_id && $allWlist[$k]->className = 'active';            
            if ($_list->is_default) {unset($allWlist[$k]);continue;}           
        }
        $defWList->list_id == $list->list_id && $defWList->className = 'active';
        sort($allWlist);
        $lists = [
            'wishlist' => [
                'd' => $defWList,
                'o' => $allWlist,
            ],
            'personal' => $persList
        ];
        $lists['all'] = [
            $persList,
            $defWList,
        ];
        $lists['all'] = array_merge($lists['all'], $allWlist);
        
        $products = [];
        if ($list){
            $q = $this->personalCatalogService->setListingProductsToContainer($list, $get);
            $cnt = $q->getCount();
            \Yii::$app->set('productsFilterQuery', $q);

            $listing_split = \frontend\design\SplitPageResults::make($q->buildQuery()->getQuery(), $maxItems, '*', 'page', $cnt);

            $ids = $this->personalCatalogService->getAppropriateList($list, $listing_split->sql_query->column());
            $products = Info::getListProductsDetails($ids, $this->settings);
            $products = $this->personalCatalogService->applyRequeredData($list, $products);
            $currencies = \Yii::$container->get('currencies');
            foreach($products as &$product){
                if ($product['product_has_attributes']){
                    $product['attributes'] = $this->describeAttributes($product);
                }
                $priceInstance = \common\models\Product\Price::getInstance($product['products_id']);                
                $special_price = $priceInstance->getInventorySpecialPrice(['qty' => 1]);
                if ( $special_price !== false) {
                    $product['final_price_formatted'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                } else {
                    $products_price = $priceInstance->getInventoryPrice(['qty' => 1]);
                    $product['final_price_formatted'] = $currencies->display_price($products_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                }
            }
        }

        return IncludeTpl::widget(['file' => 'boxes/account/personal-catalog.tpl', 
            'params' => [
                'settings' => $this->settings,
                'id' => $this->id,
                'products' => $products,
                'lists' => $lists,
                'currentList' => $list,
                'show' => $this->settings['listing_type'],
                'page_name' => $get['page_name'],
                'params' => ['params' => ['listing_split' => $listing_split]],
                'service' => $this->personalCatalogService,
        ]]);
    }
    
    //descrive attributes...again
    private function describeAttributes($product){
        $attributes = [];
        $products_id = $product['products_id'];
        if ($products_id){
            \common\helpers\Inventory::normalize_id($products_id, $atts);
            if ($atts){
                foreach ($atts as $option => $value) {
                    $option_arr = explode('-', $option);
                    $attributes_values = \common\models\ProductsAttributes::find()
                            ->alias('pa')
                            ->joinWith('productsOptions')
                            ->joinWith('productsOptionsValues')
                            ->where(['pa.products_id' => (int)($option_arr[1] > 0 ? $option_arr[1] : intval($products_id)),
                                     'pa.options_id' => (int)$option_arr[0], 
                                     'pa.options_values_id' => (int)$value])
                            ->one();
                    if ($attributes_values){
                        $attributes[$option]['products_options_name'] = $attributes_values->productsOptions->products_options_name;
                        $attributes[$option]['products_options_values_name'] = $attributes_values->productsOptionsValues->products_options_values_name;
                    }
                }
            }
        }
        return $attributes;
    }
}