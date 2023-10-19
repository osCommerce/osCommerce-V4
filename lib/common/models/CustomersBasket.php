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

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "customers_basket".
 *
 * @property integer $customers_basket_id
 * @property integer $customers_id
 * @property integer $basket_id
 * @property integer $platform_id
 * @property integer $language_id
 * @property string $currency
 * @property string $products_id
 * @property integer $customers_basket_quantity
 * @property string $final_price
 * @property string $customers_basket_date_added
 * @property integer $is_giveaway
 * @property integer $is_pack
 * @property integer $unit
 * @property integer $pack_unit
 * @property integer $packaging
 * @property integer $gaw_id
 * @property integer $is_temlplate
 * @property string $parent_product
 * @property string $elements
 */

class CustomersBasket extends ActiveRecord {

    public static function tableName() {
        return 'customers_basket';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['customers_basket_date_added'],
                ],              
                 'value' => date('Ymd'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customers_basket_id' => 'Customers Basket ID',
            'customers_id' => 'Customers ID',
            'basket_id' => 'Basket ID',
            'platform_id' => 'Platform ID',
            'language_id' => 'Language ID',
            'currency' => 'Currency',
            'products_id' => 'Products ID',
            'customers_basket_quantity' => 'Customers Basket Quantity',
            'final_price' => 'Final Price',
            'customers_basket_date_added' => 'Customers Basket Date Added',
            'is_giveaway' => 'Is Giveaway',
            'is_pack' => 'Is Pack',
            'unit' => 'Unit',
            'pack_unit' => 'Pack Unit',
            'packaging' => 'Packaging',
            'gaw_id' => 'Gaw ID',
            'is_temlplate' => 'Is Temlplate',
            'parent_product' => 'Parent Product',
            'elements' => 'Elements',
        ];
    }

    public function getProductAttributes() {
        return $this->hasMany(CustomersBasketAttributes::className(), ['products_id' => 'products_id', 'customers_id' => 'customers_id', 'basket_id' => 'basket_id']);
    }
    
    /*$product is element of shopping cart content*/
    public static function saveProduct( $product, $customer_id, $cart ){
        
        if ($product && $customer_id){
            $products_id = key($product);
            $val = current($product);
            $qty = (int) $val['qty'];
            $productBasket_query = self::find()->where([
                'customers_id' => (int) $customer_id,
                'products_id' => $products_id,                
            ]);
            $multi_customer_id = 0;
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    }
                    $productBasket_query->andWhere(['multi_email_id' => $multi_customer_id]);
                }
            }
            if (isset($val['gaw_id']) && $val['gaw_id']){
                $productBasket_query->andWhere(['is_giveaway' => 1]);
            } else {
                $productBasket_query->andWhere(['is_giveaway' => 0]);
            }
            
            if( $multiCart = \common\helpers\Extensions::isAllowed('MultiCart') ) {
                $multiCart::productsOfCart($productBasket_query, $cart->basketID);
            }
            $productBasket = $productBasket_query->one();
            
            if (!$productBasket){
                $productBasket = new self();
            } else {
                $qty = \common\helpers\Product::filter_product_order_quantity($products_id, /*$productBasket->customers_basket_quantity + */$qty);
            }
            $productBasket->setAttributes([
                'customers_id' => (int) $customer_id,
                'products_id' => tep_db_input($products_id),
                'customers_basket_quantity' => $qty,
                'parent_product' => strval($val['parent']),
                'relation_type' => (isset($val['relation_type'])?strval($val['relation_type']):''),
                'basket_id' => (int)$cart->basketID,
                'platform_id' => (isset($val['platform_id'])?strval($val['platform_id']):(int)\common\classes\platform::currentId()),
                'language_id' => (int)$cart->language_id,
                'currency' => $cart->currency,
                'is_giveaway' => 0,
                'multi_email_id' => $multi_customer_id,
            ], false);
            //givw away
            if (isset($val['gaw_id']) && $val['gaw_id']){
                $productBasket->is_giveaway = 1;
                $productBasket->gaw_id = $val['gaw_id'];
            }
            
            $productBasket->setAttribute('props', isset($val['props'])?$val['props']:'');
            
            if (($val['is_pack'] ?? false)){
                $productBasket->setAttributes([
                    'is_pack' => 1,
                    'unit' => (int)$val['unit'],
                    'pack_unit' => (int)$val['pack_unit'],
                    'packaging' => (int)$val['packaging'],
                    ], false);
            }            
            
            if (!$productBasket->hasErrors()){
                if ($productBasket->save(false)){
                    CustomersBasketAttributes::saveProductAttributes($product, $customer_id, $cart);
                    if (isset($val['gift_wrap']) && !CustomersBasketAttributes::hasGiftWrapAttribute($products_id, $customer_id, $cart->basketID)) {
                        $product[$products_id]['attributes'] = ['gift_wrap' => $val['gift_wrap'] ];
                        CustomersBasketAttributes::saveProductAttributes($product, $customer_id, $cart);
                    }
                }
            }
        }        
    }
    
    public static function getProducts( $customer_id, $basket_id = null ){
        $productBasket = [];
        if ($customer_id){
            $productBasket_query = CustomersBasket::find()->where('customers_id = "' . (int) $customer_id . '" and is_giveaway = 0');

            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    } else {
                        $multi_customer_id = 0;
                    }
                    $productBasket_query->andWhere(['multi_email_id' => $multi_customer_id]);
                }
            }
            
            /* filter by current frontend */
            if (defined('SHOPPING_CART_SHARE') && \common\classes\platform::activeId() && SHOPPING_CART_SHARE == 'False') {
              $productBasket_query->andWhere(['platform_id' => \common\classes\platform::activeId()]);
            }

            /** @var \common\extensions\MultiCart\MultiCart  $multiCart */
            if( $multiCart = \common\helpers\Extensions::isAllowed('MultiCart') ) {
                $multiCart::productsOfCart($productBasket_query, $basket_id);
            }
            
            $productBasket = $productBasket_query->with('productAttributes')
                    ->asArray()->all();
        }
        
        return $productBasket;
    }
    
    public function beforeDelete() {
        if (!$this->is_giveaway){
            $multi_customer_id = 0;
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    }
                }
            }
            CustomersBasketAttributes::deleteAll([
                'customers_id' => (int) $this->customers_id,
                'products_id' => $this->products_id,
                'basket_id' => (int)$this->basket_id,
                'multi_email_id' => $multi_customer_id,
            ]);
        }
        return parent::beforeDelete();
    }
    
    public static function deleteProduct($customer_id, $products_id, $is_giveaway = 0){
        if ($customer_id && $products_id){
            
            $productBasket_query = self::find()->where(['customers_id' => (int) $customer_id, 'products_id' => $products_id]);
            
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    } else {
                        $multi_customer_id = 0;
                    }
                    $productBasket_query->andWhere(['multi_email_id' => $multi_customer_id]);
                }
            }
            
            if ($is_giveaway){
                $productBasket_query->andWhere(['is_giveaway' => 1]);
            }
            if( $multiCart = \common\helpers\Extensions::isAllowed('MultiCart') ) {
                $multiCart::productsOfCart($productBasket_query);
            }
            $productBasket = $productBasket_query->one();
            if ($productBasket){
                $productBasket->delete();
            }                        
        }
    }
    
    public static function clearBasket($customer_id, $is_giveaway = 0, $basket_id=null){
        if ($customer_id){
            $productBasket_query = self::find()->where(['customers_id' => (int) $customer_id]);

            if (defined('SHOPPING_CART_SHARE') && \common\classes\platform::activeId() && SHOPPING_CART_SHARE == 'False') {
              $productBasket_query->andWhere(['platform_id' => \common\classes\platform::activeId()]);
            }
            
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    } else {
                        $multi_customer_id = 0;
                    }
                    $productBasket_query->andWhere(['multi_email_id' => $multi_customer_id]);
                }
            }

            if ($is_giveaway){
                $productBasket_query->andWhere(['is_giveaway' => 1]);
            }

            if( $multiCart = \common\helpers\Extensions::isAllowed('MultiCart') ) {
                $multiCart::productsOfCart($productBasket_query, $basket_id);
            }

            $productBasket = $productBasket_query->all();
            if ($productBasket){
                foreach($productBasket as $product){
                    $product->delete();
                }
            }
        }
    }
    
    public static function clearGiveaway($customer_id){
        if ($customer_id){            
            self::clearBasket($customer_id, 1);
        }
    }
    
    public static function getBasketId($customer_id) {
        if ($customer_id > 0){
            $additional = "";
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
                if($CustomersMultiEmails::allowed()) {
                    $is_multi = \Yii::$app->get('storage')->get('is_multi');
                    if ($is_multi) {
                        $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                    } else {
                        $multi_customer_id = 0;
                    }
                    $additional = " and multi_email_id = '" . (int) $multi_customer_id . "'";
                }
            }
            $b = self::find()->where("customers_id = '" . (int) $customer_id . "'" . $additional)->distinct()->one();
            if ($b) return $b->basket_id;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getlanguage()
    {
        return $this->hasOne(Languages::className(), ['language_id' => 'languages_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['customers_id' => 'customers_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::className(), ['platform_id' => 'platform_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Products::className(), ['products_id' => 'products_id']);
    }

    /**
     * @param int $customers_id
     * @param string $products_id
     * @param int $customers_basket_quantity
     * @param string $parent_product
     * @param int $is_giveaway
     * @param int $gaw_id
     * @param int $is_pack
     * @param int $unit
     * @param int $pack_unit
     * @param int $packaging
     * @param int $basket_id
     * @param int $platform_id
     * @param int $language_id
     * @param string $currency
     * @param float $final_price
     * @param int $is_temlplate
     * @param string $elements
     * @return array|CustomersBasket|null|ActiveRecord
     */
    public static function create($customers_id=0,$products_id='0',$customers_basket_quantity=0,$parent_product='',$is_giveaway=0,$gaw_id=0,$is_pack=0,$unit=0,$pack_unit=0,$packaging=0,$basket_id=0,$platform_id=0,$language_id=0,$currency='',$final_price=0.00,$is_temlplate=0,$elements='')
    {
        if((int)$customers_id < 1 || $customers_basket_quantity < 1 ){
            throw new \DomainException('wrong input data');
        }
        $model = static::find()->where(['customers_id'=>$customers_id,'products_id'=>$products_id ])->limit(1)->one();
        if(is_null($model)){
            $model = new static();
        }
        $model->customers_basket_date_added = date('Ymd');
        $model->customers_id = $customers_id;
        $model->products_id = $products_id;
        $model->customers_basket_quantity = $customers_basket_quantity;
        $model->parent_product = $parent_product;
        $model->is_giveaway = $is_giveaway;
        $model->gaw_id = $gaw_id;
        $model->is_pack =$is_pack;
        $model->unit = $unit;
        $model->pack_unit = $pack_unit;
        $model->packaging = $packaging;
        $model->basket_id = $basket_id;
        $model->platform_id = $platform_id;
        $model->language_id = $language_id;
        $model->currency = $currency;
        $model->final_price = $final_price;
        $model->is_temlplate = $is_temlplate;
        $model->elements = $elements;
        if ($CustomersMultiEmails = \common\helpers\Acl::checkExtension('CustomersMultiEmails', 'allowed')) {
            if($CustomersMultiEmails::allowed()) {
                $is_multi = \Yii::$app->get('storage')->get('is_multi');
                if ($is_multi) {
                    $multi_customer_id = (int)\Yii::$app->get('storage')->get('multi_customer_id');
                } else {
                    $multi_customer_id = 0;
                }
                $model->multi_email_id = $multi_customer_id;
            }
        }
        return $model;
    }
}
