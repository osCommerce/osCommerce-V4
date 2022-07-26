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

namespace common\components;

use Yii;
use common\components\ProductItem;
use common\helpers\Tax;
use common\helpers\Product;

class ProductsContainer
{
    private $container = [
        //'single' => [],
        //'list' => [],
    ];   

    CONST TYPE_MANUFACTURER = 'manufacturer';
    CONST TYPE_STOCK = 'stock';
    
    private function init(){
        $this->container = [
           //'single' => [],
           //'list' => [],
        ];   
    }

/**
 * ... no method to populate ['single'] container from array
 * populate ['list'] container from params array (1 product, not many), or query and fill in ['single'] container
 * @global integer $languages_id
 * @param array $params ['products_id' => NN] or all details to save in ['list'] container
 * @return $this
 */
    public function loadProducts(array $params = []){
        
        if (isset($params['products_id']) && count($params)== 1){
            if (!$this->has($params['products_id'])){
                $this->_preloadProduct($params['products_id']);
                if ($this->has($params['products_id'])){
                    $price_product_id = \common\helpers\Product::normalizePricePrid($params['products_id']);
                    $this->container[$params['products_id']]->attachDetails([
                        'special_price' => Product::get_products_special_price($price_product_id),
                        'products_price' => Product::get_products_price($price_product_id, 1),
                        'settings' => Product::getSettings($price_product_id),
                    ]);
                }
            }
        } elseif(!empty($params['products_id'])) {
            if (isset($this->container[$params['products_id']]) && $this->container[$params['products_id']] instanceof ProductItem){
                $diff = @array_diff_assoc($params, (array)$this->container[$params['products_id']]);
                if (count($diff)) {
                    $this->container[$params['products_id']]->attachDetails($diff);
                }
            } else {
              $this->container[$params['products_id']] = new ProductItem($params, \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        
        return $this;
    }
    
    /**
     * Check if product is existed
     * @param type $products_id
     */
    public function has($products_id){
        return isset($this->container[$products_id]) && $this->container[$products_id] instanceof ProductItem;
    }
    
    protected function _preloadProduct($products_id){
        if ($products_id){
            $product = \common\models\Products::find()->alias("p")->where(['p.products_id' => $products_id])
                    ->with(['listingDescription' => function(\yii\db\ActiveQuery $query){
                        $query->addSelect(['products_image_alt_tag_mask', 'products_head_desc_tag', 'products_image_title_tag_mask']);
                    }])->one();
            if ($product){
                $data = $product->getAttributes();
                $data['_p_products_price'] = $data['products_price'];
                $data['products_price_main'] = $data['products_price'];
                foreach(['products_model', 'products_ean', 'products_isbn', 'products_asin', 'products_upc'] as $key){
                    $data[preg_replace("/products_/", "", $key)] = $data[$key];
                }
                if (is_array($product->relatedRecords)){
                    foreach($product->relatedRecords['listingDescription'] as $relation){
                        foreach($relation->getAttributes() as $name => $value){
                            if (in_array($name, ['noindex_option', 'nofollow_option', 'rel_canonical'])){
                                $data[$name] = $data[$name] ?? $value;
                            } else {
                                $data[$name] = (!empty($data[$name]) ? $data[$name] : $value);
                            }
                        }
                    }
                }
                $data['products_description'] = \common\classes\TlUrl::replaceUrl($data['products_description'] ?? null);
                $data['products_description_short'] = \common\classes\TlUrl::replaceUrl($data['products_description_short'] ?? null);
                $data['tax_rate'] = Tax::get_tax_rate($product['products_tax_class_id'] ?? null);
                
                $this->container[$products_id] = new ProductItem($data, \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        
        /*$product = tep_db_fetch_array(tep_db_query("
                        select p.products_id, p.products_price, p.products_tax_class_id, p.manufacturers_id,
                        p.products_price as _p_products_price,
                        p.is_listing_product, p.parent_products_id, p.products_id_price, p.products_id_stock, p.cart_button,
                        p.without_inventory, 
                        p.dimensions_cm, p.length_cm, p.width_cm, p.height_cm, p.weight_cm, 
                        if(length(pd1.products_description) > 0, pd1.products_description, pd.products_description) as products_description, 
                        if(length(pd1.products_description_short) > 0, pd1.products_description_short, pd.products_description_short) as products_description_short, 
                        if(length(pd1.products_name) > 0, pd1.products_name, pd.products_name) as products_name, 
                        if(length(pd1.products_head_title_tag) > 0, pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag, 
                        if(length(pd1.products_head_desc_tag) > 0, pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag, 
                        if(length(pd1.products_h1_tag) > 0, pd1.products_h1_tag, pd.products_h1_tag) as products_h1_tag, 
                        if(length(pd1.products_h2_tag) > 0, pd1.products_h2_tag, pd.products_h2_tag) as products_h2_tag, 
                        if(length(pd1.products_h3_tag) > 0, pd1.products_h3_tag, pd.products_h3_tag) as products_h3_tag, 
                        if(length(pd1.products_image_alt_tag_mask) > 0, pd1.products_image_alt_tag_mask, pd.products_image_alt_tag_mask) as products_image_alt_tag_mask, 
                        if(length(pd1.products_image_title_tag_mask) > 0, pd1.products_image_title_tag_mask, pd.products_image_title_tag_mask) as products_image_title_tag_mask, 
                        p.products_model as model, p.products_ean as ean, p.products_isbn as isbn, p.products_asin as asin, p.products_upc as upc,
                        p.products_pctemplates_id, p.is_bundle,
                        pd1.noindex_option, pd1.nofollow_option, pd1.rel_canonical
                        from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.platform_id = '".intval(Yii::$app->get('platform')->config()->getPlatformToDescription())."' and pd1.language_id = '" . (int)$languages_id . "', " . TABLE_PRODUCTS . " p
                        where p.products_id = '" . (int)$products_id . "' and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."'
                            and pd.products_id = p.products_id
                        "));
                if ($product) {
                  $product['products_description'] = \common\classes\TlUrl::replaceUrl($product['products_description']);
                  $product['products_description_short'] = \common\classes\TlUrl::replaceUrl($product['products_description_short']);

                  $price_product_id = \common\helpers\Product::normalizePricePrid($products_id);
                  $this->container['single'][$params['products_id']] = new ProductItem($product, \ArrayObject::ARRAY_AS_PROPS);
                  $this->container['single'][$params['products_id']]['tax_rate'] = Tax::get_tax_rate($product['products_tax_class_id']);
                  $this->container['single'][$params['products_id']]['special_price'] = Product::get_products_special_price($price_product_id);
                  $this->container['single'][$params['products_id']]['products_price'] = Product::get_products_price($price_product_id, 1, $product['products_price']);
                  $this->container['single'][$params['products_id']]['settings'] = Product::getSettings($price_product_id);
                }*/
    }

    /**
    * load list
    * @param array $products
    * @return $this
    */
    public function loadList($products){
      if (is_array($products)) {
        foreach ($products as $params) {
          if(!empty($params['products_id'])) {
            $this->loadProducts($params);
          }
        }
      }
      return $this;
    }


    public function clearContainer(){
        $this->init();
        return $this;
    }
    
    public function checkAttachedDetails($products_id, $block){
        return isset($this->container[$products_id][$block]);
    }
    
/**
 * attach extra details to the product in container.
 * use to fill in ['single'] container
 * ??? is it required ??? If product is not set in the container - load (using loadProducts) it first (add description, tax rate, special, and product price)
 * @param integer $products_id
 * @param array $array
 * @return $this
 */
    public function attachDetails($products_id, $array){
        if (!is_array($array)) return $this;
        
        if ( $this->has($products_id) ) {
            $this->container[$products_id]->attachDetails($array);
        } else {
            $this->_preloadProduct(['products_id' => $products_id]);
            $this->container[$products_id]->attachDetails($array);
        }

        return $this;
    }

/**
 * get all product from list container or refill $listing_type from the list container
 * @param string $listing_type
 * @return array of ProductItem
 */
    public function getAllProducts($listing_type = ''){
        if (!empty($listing_type)){
            $map = [];
            $use_sort = false;
            foreach($this->container as $prid => $array){
                if ($array['listing_type'] == $listing_type) {
                    $map[$prid] = $array->getArrayCopy();
                    if (is_array($map[$prid]) && isset($map[$prid]['sort_order'])){
                        $use_sort = true;
                    }
                }
            }
            if ($map && $use_sort){
                \yii\helpers\ArrayHelper::multisort($map, 'sort_order');
            }
            return $map;
        }
        return $this->container;
    }

/**
 * get product's details from container (priority Single, List)
 * @param integer $products_id
 * @return ProductItem|bool
 */
    public function getProduct($products_id){
        if ($this->has($products_id)){
            return $this->container[$products_id];
        }
        $this->_preloadProduct($products_id);
        if ($this->has($products_id)){
            return $this->container[$products_id];
        }
        return false;
    }
}