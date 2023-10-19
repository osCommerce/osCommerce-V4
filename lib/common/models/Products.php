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


use common\models\Product\ProductsNotes;
use common\models\queries\ProductsQuery;
use yii\db\ColumnSchema;
use \yii\db\Expression;

/**
 * This is the model class for table "products".
 *
 * @property int $products_id
 * @property int $parent_products_id
 * @property int $products_id_stock
 * @property int $products_id_price
 * @property int $products_quantity
 * @property int $allocated_stock_quantity
 * @property int $temporary_stock_quantity
 * @property int $warehouse_stock_quantity
 * @property int $suppliers_stock_quantity
 * @property int $ordered_stock_quantity
 * @property string $products_model
 * @property string $products_image
 * @property string $products_image_med
 * @property string $products_image_lrg
 * @property string $products_image_sm_1
 * @property string $products_image_xl_1
 * @property string $products_image_sm_2
 * @property string $products_image_xl_2
 * @property string $products_image_sm_3
 * @property string $products_image_xl_3
 * @property string $products_image_sm_4
 * @property string $products_image_xl_4
 * @property string $products_image_sm_5
 * @property string $products_image_xl_5
 * @property string $products_image_sm_6
 * @property string $products_image_xl_6
 * @property string $products_price
 * @property string $products_date_added
 * @property string $products_last_modified
 * @property string $products_date_available
 * @property string $products_weight
 * @property int $products_status
 * @property int $manual_control_status
 * @property int $products_tax_class_id
 * @property int $manufacturers_id
 * @property int $products_ordered
 * @property string $products_seo_page_name
 * @property string $products_old_seo_page_name
 * @property string $products_image_alt_1
 * @property string $products_image_alt_2
 * @property string $products_image_alt_3
 * @property string $products_image_alt_4
 * @property string $products_image_alt_5
 * @property string $products_image_alt_6
 * @property int $sort_order
 * @property string $products_file
 * @property string $products_price_discount
 * @property int $vendor_id
 * @property int $previous_status
 * @property string $last_xml_import
 * @property string $last_xml_export
 * @property string $products_sets_discount
 * @property string $products_ean
 * @property string $products_asin
 * @property string $products_isbn
 * @property string $bonus_points_price
 * @property string $bonus_points_cost
 * @property int $is_virtual
 * @property string $dimensions_cm
 * @property string $length_cm
 * @property string $width_cm
 * @property string $height_cm
 * @property string $weight_cm
 * @property string $dimensions_in
 * @property string $length_in
 * @property string $width_in
 * @property string $height_in
 * @property string $weight_in
 * @property string $inner_carton_size
 * @property string $inner_carton_dimensions_cm
 * @property string $inner_length_cm
 * @property string $inner_width_cm
 * @property string $inner_height_cm
 * @property string $inner_weight_cm
 * @property string $inner_carton_dimensions_in
 * @property string $inner_length_in
 * @property string $inner_width_in
 * @property string $inner_height_in
 * @property string $inner_weight_in
 * @property string $outer_carton_size
 * @property string $outer_carton_dimensions_cm
 * @property string $outer_length_cm
 * @property string $outer_width_cm
 * @property string $outer_height_cm
 * @property string $outer_weight_cm
 * @property string $outer_carton_dimensions_in
 * @property string $outer_length_in
 * @property string $outer_width_in
 * @property string $outer_height_in
 * @property string $outer_weight_in
 * @property int $pack_unit
 * @property int $packaging
 * @property string $products_sets_price
 * @property string $shipping_surcharge_price
 * @property int $order_quantity_minimal
 * @property int $order_quantity_step
 * @property int $item_in_virtual_item_qty
 * @property string $item_in_virtual_item_step
 * @property string $products_upc
 * @property int $subscription
 * @property string $subscription_code
 * @property int $products_price_full
 * @property int $stock_indication_id
 * @property int $stock_delivery_terms_id
 * @property string $products_price_pack_unit
 * @property string $products_price_discount_pack_unit
 * @property string $products_price_packaging
 * @property string $products_price_discount_packaging
 * @property int $products_pctemplates_id
 * @property int $product_designer_template_id
 * @property int $component_only
 * @property int $elements_included
 * @property string $products_price_configurator
 * @property string $products_discount_configurator
 * @property int $products_groups_id
 * @property int $request_quote
 * @property int $request_quote_out_stock
 * @property int $ask_sample
 * @property int $cart_button
 * @property int $disable_discount
 * @property string $products_popularity
 * @property string $popularity_simple
 * @property string $popularity_bestseller
 * @property int $created_by_platform_id
 * @property int $use_sets_discount
 * @property int $maps_id
 * @property int $is_bundle
 * @property string $products_sets_price_formula
 * @property int $stock_control
 * @property int $disable_children_discount
 *
 * @property Groups[] $groups
 */
class Products extends \yii\db\ActiveRecord
{
  public static $listingDescriptionFields = ['products_name', 'products_description', 'products_url', 'products_head_title_tag', 'products_description_short', 'products_seo_page_name', 'products_h1_tag', 'products_h2_tag', 'products_h3_tag', 'products_internal_name' ];
  public static $searchDescriptionFields =  ['products_name', 'products_description', 'products_url', 'products_head_title_tag', 'products_head_desc_tag', 'products_head_keywords_tag', 'products_description_short', 'products_seo_page_name', 'products_h1_tag', 'products_h2_tag', 'products_h3_tag', 'products_internal_name'];
  public static $searchFields =  ['products_model', 'products_seo_page_name', 'products_ean', 'products_asin', 'products_isbn', 'products_upc' ];
  public static $searchInventoryFields =  ['products_model', 'products_ean', 'products_asin', 'products_isbn', 'products_upc' ];

    protected $_recalculate_sub_product_parent_id = null;

  /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_quantity', 'allocated_stock_quantity', 'temporary_stock_quantity', 'warehouse_stock_quantity', 'suppliers_stock_quantity', 'ordered_stock_quantity', 'products_status', 'manual_control_status', 'products_tax_class_id', 'manufacturers_id', 'products_ordered', 'sort_order', 'vendor_id', 'previous_status', 'is_virtual', 'pack_unit', 'packaging', 'order_quantity_minimal', 'order_quantity_step', 'item_in_virtual_item_qty', 'subscription', 'products_price_full', 'stock_indication_id', 'stock_delivery_terms_id', 'products_pctemplates_id', 'product_designer_template_id', 'component_only', 'elements_included', 'products_groups_id', 'request_quote', 'request_quote_out_stock', 'ask_sample', 'cart_button', 'disable_discount', 'created_by_platform_id', 'use_sets_discount', 'maps_id', 'is_bundle'], 'integer'],
            [['products_price', 'products_weight', 'products_sets_discount', 'bonus_points_price', 'bonus_points_cost', 'length_cm', 'width_cm', 'height_cm', 'weight_cm', 'length_in', 'width_in', 'height_in', 'weight_in', 'inner_length_cm', 'inner_width_cm', 'inner_height_cm', 'inner_weight_cm', 'inner_length_in', 'inner_width_in', 'inner_height_in', 'inner_weight_in', 'outer_length_cm', 'outer_width_cm', 'outer_height_cm', 'outer_weight_cm', 'outer_length_in', 'outer_width_in', 'outer_height_in', 'outer_weight_in', 'products_sets_price', 'shipping_surcharge_price', 'products_price_pack_unit', 'products_price_packaging', 'products_price_configurator', 'products_discount_configurator', 'products_popularity', 'popularity_simple', 'popularity_bestseller'], 'number'],
            [['products_date_added', 'products_last_modified', 'products_date_available', 'products_new_until', 'last_xml_import', 'last_xml_export'], 'safe'],
            [['products_model', 'products_seo_page_name', 'products_old_seo_page_name', 'products_file', 'products_price_discount', 'products_price_discount_pack_unit', 'products_price_discount_packaging'], 'string', 'max' => 255],
            [['item_in_virtual_item_step'], 'string', 'max' => 250],
            [['products_image', 'products_image_med', 'products_image_lrg', 'products_image_sm_1', 'products_image_xl_1', 'products_image_sm_2', 'products_image_xl_2', 'products_image_sm_3', 'products_image_xl_3', 'products_image_sm_4', 'products_image_xl_4', 'products_image_sm_5', 'products_image_xl_5', 'products_image_sm_6', 'products_image_xl_6', 'products_image_alt_1', 'products_image_alt_2', 'products_image_alt_3', 'products_image_alt_4', 'products_image_alt_5', 'products_image_alt_6'], 'string', 'max' => 64],
            [['products_ean', 'products_isbn'], 'string', 'max' => 13],
            [['products_asin'], 'string', 'max' => 10],
            [['dimensions_cm', 'dimensions_in', 'inner_carton_size', 'inner_carton_dimensions_cm', 'inner_carton_dimensions_in', 'outer_carton_size', 'outer_carton_dimensions_cm', 'outer_carton_dimensions_in', 'subscription_code'], 'string', 'max' => 32],
            [['products_upc'], 'string', 'max' => 14],
            [['products_sets_price_formula'], 'string', 'max' => 1024],
            [['disable_children_discount'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_id' => 'Products ID',
            'products_quantity' => 'Products Quantity',
            'allocated_stock_quantity' => 'Allocated Stock Quantity',
            'temporary_stock_quantity' => 'Temporary Stock Quantity',
            'warehouse_stock_quantity' => 'Warehouse Stock Quantity',
            'suppliers_stock_quantity' => 'Suppliers Stock Quantity',
            'ordered_stock_quantity' => 'Ordered Stock Quantity',
            'products_model' => 'Products Model',
            'products_image' => 'Products Image',
            'products_image_med' => 'Products Image Med',
            'products_image_lrg' => 'Products Image Lrg',
            'products_image_sm_1' => 'Products Image Sm 1',
            'products_image_xl_1' => 'Products Image Xl 1',
            'products_image_sm_2' => 'Products Image Sm 2',
            'products_image_xl_2' => 'Products Image Xl 2',
            'products_image_sm_3' => 'Products Image Sm 3',
            'products_image_xl_3' => 'Products Image Xl 3',
            'products_image_sm_4' => 'Products Image Sm 4',
            'products_image_xl_4' => 'Products Image Xl 4',
            'products_image_sm_5' => 'Products Image Sm 5',
            'products_image_xl_5' => 'Products Image Xl 5',
            'products_image_sm_6' => 'Products Image Sm 6',
            'products_image_xl_6' => 'Products Image Xl 6',
            'products_price' => 'Products Price',
            'products_date_added' => 'Products Date Added',
            'products_last_modified' => 'Products Last Modified',
            'products_date_available' => 'Products Date Available',
            'products_new_until' => 'Mark as new until',
            'products_weight' => 'Products Weight',
            'products_status' => 'Products Status',
            'manual_control_status' => 'Manual Control Status',
            'products_tax_class_id' => 'Products Tax Class ID',
            'manufacturers_id' => 'Manufacturers ID',
            'products_ordered' => 'Products Ordered',
            'products_seo_page_name' => 'Products Seo Page Name',
            'products_old_seo_page_name' => 'Products Old Seo Page Name',
            'products_image_alt_1' => 'Products Image Alt 1',
            'products_image_alt_2' => 'Products Image Alt 2',
            'products_image_alt_3' => 'Products Image Alt 3',
            'products_image_alt_4' => 'Products Image Alt 4',
            'products_image_alt_5' => 'Products Image Alt 5',
            'products_image_alt_6' => 'Products Image Alt 6',
            'sort_order' => 'Sort Order',
            'products_file' => 'Products File',
            'products_price_discount' => 'Products Price Discount',
            'vendor_id' => 'Vendor ID',
            'previous_status' => 'Previous Status',
            'last_xml_import' => 'Last Xml Import',
            'last_xml_export' => 'Last Xml Export',
            'products_sets_discount' => 'Products Sets Discount',
            'products_ean' => 'Products Ean',
            'products_asin' => 'Products Asin',
            'products_isbn' => 'Products Isbn',
            'bonus_points_price' => 'Bonus Points Price',
            'bonus_points_cost' => 'Bonus Points Cost',
            'is_virtual' => 'Is Virtual',
            'dimensions_cm' => 'Dimensions Cm',
            'length_cm' => 'Length Cm',
            'width_cm' => 'Width Cm',
            'height_cm' => 'Height Cm',
            'weight_cm' => 'Weight Cm',
            'dimensions_in' => 'Dimensions In',
            'length_in' => 'Length In',
            'width_in' => 'Width In',
            'height_in' => 'Height In',
            'weight_in' => 'Weight In',
            'inner_carton_size' => 'Inner Carton Size',
            'inner_carton_dimensions_cm' => 'Inner Carton Dimensions Cm',
            'inner_length_cm' => 'Inner Length Cm',
            'inner_width_cm' => 'Inner Width Cm',
            'inner_height_cm' => 'Inner Height Cm',
            'inner_weight_cm' => 'Inner Weight Cm',
            'inner_carton_dimensions_in' => 'Inner Carton Dimensions In',
            'inner_length_in' => 'Inner Length In',
            'inner_width_in' => 'Inner Width In',
            'inner_height_in' => 'Inner Height In',
            'inner_weight_in' => 'Inner Weight In',
            'outer_carton_size' => 'Outer Carton Size',
            'outer_carton_dimensions_cm' => 'Outer Carton Dimensions Cm',
            'outer_length_cm' => 'Outer Length Cm',
            'outer_width_cm' => 'Outer Width Cm',
            'outer_height_cm' => 'Outer Height Cm',
            'outer_weight_cm' => 'Outer Weight Cm',
            'outer_carton_dimensions_in' => 'Outer Carton Dimensions In',
            'outer_length_in' => 'Outer Length In',
            'outer_width_in' => 'Outer Width In',
            'outer_height_in' => 'Outer Height In',
            'outer_weight_in' => 'Outer Weight In',
            'pack_unit' => 'Pack Unit',
            'packaging' => 'Packaging',
            'products_sets_price' => 'Products Sets Price',
            'shipping_surcharge_price' => 'Shipping Surcharge Price',
            'order_quantity_minimal' => 'Order Quantity Minimal',
            'order_quantity_step' => 'Order Quantity Step',
            'item_in_virtual_item_qty' => 'Item In Virtual Item Qty',
            'item_in_virtual_item_step' => 'Item In Virtual Item Step',
            'products_upc' => 'Products Upc',
            'subscription' => 'Subscription',
            'subscription_code' => 'Subscription Code',
            'products_price_full' => 'Products Price Full',
            'stock_indication_id' => 'Stock Indication ID',
            'stock_delivery_terms_id' => 'Stock Delivery Terms ID',
            'products_price_pack_unit' => 'Products Price Pack Unit',
            'products_price_discount_pack_unit' => 'Products Price Discount Pack Unit',
            'products_price_packaging' => 'Products Price Packaging',
            'products_price_discount_packaging' => 'Products Price Discount Packaging',
            'products_pctemplates_id' => 'Products Pctemplates ID',
            'product_designer_template_id' => 'Product Designer Template ID',
            'component_only' => 'Component Only',
            'elements_included' => 'Elements Included',
            'products_price_configurator' => 'Products Price Configurator',
            'products_discount_configurator' => 'Products Discount Configurator',
            'products_groups_id' => 'Products Groups ID',
            'request_quote' => 'Request Quote',
            'request_quote_out_stock' => 'Request Quote Out Stock',
            'ask_sample' => 'Ask Sample',
            'cart_button' => 'Cart Button',
            'disable_discount' => 'Disable Discount',
            'products_popularity' => 'Products Popularity',
            'popularity_simple' => 'Popularity Simple',
            'popularity_bestseller' => 'Popularity Bestseller',
            'created_by_platform_id' => 'Created By Platform ID',
            'use_sets_discount' => 'Use Sets Discount',
            'maps_id' => 'Maps ID',
            'is_bundle' => 'Is Bundle',
            'products_sets_price_formula' => 'Products Sets Price Formula',
            'disable_children_discount' => 'Disable Children Discount'
        ];
    }

    public static function findByVar($productOrId)
    {
        if ($productOrId instanceof self) {
            return $productOrId;
        } elseif (is_numeric($productOrId)) {
            return self::findOne(['products_id' => (int) \common\helpers\Inventory::get_prid($productOrId)]);
        }
    }

    /**
     * @param Products|int $productOrId
     * @return Products
     * @throws \Exception
     */
    public static function findByVarCheck($productOrId)
    {
        $res = self::findByVar($productOrId);
        \common\helpers\Assert::instanceOf($productOrId, self::class);
        return $res;
    }


// personal_catalolog moved to extension. relation is used nowhere in osc and extensions but maybe somethere in old projects?
//    public function getCustomers()
//    {
//        return $this->hasMany(\common\models\Customers::class, ['customers_id' => 'customers_id'])
//            ->viaTable('personal_catalog', ['products_id' => 'products_id']);
//    }

    /**
     * one-to-many all languages 1 platform (current)
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(ProductsDescription::class, ['products_id' => 'products_id'])
                ->andOnCondition(['platform_id' => \common\classes\platform::currentId()]);
    }

    /**
     * one-to-many all languages all platforms
     * @return \yii\db\ActiveQuery
     */
    public function getProductsDescriptions()
    {
        return $this->hasMany(ProductsDescription::class, ['products_id' => 'products_id']);
    }

    /**
     * one-to-one 1 language 1 platform (current)
     * @return \yii\db\ActiveQuery
     */
    public function getDescription()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasOne(ProductsDescription::class, ['products_id' => 'products_id'])
                ->andOnCondition(['language_id' => (int)$languages_id, 'platform_id' => \common\classes\platform::currentId()]);
    }

    /**
     * one-to-many 1 language 2 platforms (current & default)
     * @return \yii\db\ActiveQuery
     */
    public function getListingDescription()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasMany(ProductsDescription::class, ['products_id' => 'products_id'])

                ->select(self::$listingDescriptionFields)
                ->addSelect(['platform_id', 'products_id', 'language_id'])
                ->addSelect(['main' => new Expression('platform_id= :currentPlatformId', [':currentPlatformId' => \common\classes\platform::currentId()])])
                ->where(['language_id' => (int)$languages_id,
                         'platform_id' => [intval(\common\classes\platform::defaultId()), intval(\Yii::$app->get('platform')->config()->getPlatformToDescription())]
                  ])
                ->orderBy('main desc')

            ;
    }

    public function getListingName() {
      $languages_id = \Yii::$app->settings->get('languages_id');
      $platform_id = \common\classes\platform::currentId();
      $platform_id = (new \common\classes\platform_settings($platform_id))->getPlatformToDescription();

      /**
       * @var $search \common\extensions\PlainProductsDescription\models\PlainProductsNameSearch
       * @var $toProducts \common\extensions\PlainProductsDescription\models\PlainProductsNameToProducts
       */
      $search = \common\helpers\Extensions::getModel('PlainProductsDescription', 'PlainProductsNameSearch');
      $toProducts = \common\helpers\Extensions::getModel('PlainProductsDescription', 'PlainProductsNameToProducts');
      if ( !empty($search) && !empty($toProducts) ) {
        return $this->hasOne($search, ['id' => 'plain_id'])
            ->andWhere([$search::tableName() . '.language_id' => (int)$languages_id])
            ->viaTable($toProducts::tableName(), ['products_id' => 'products_id'], function ($query) use($platform_id, $toProducts) {$query->andOnCondition([$toProducts::tableName() .'.platform_id' => $platform_id]);});

      } else {
        return $this->hasOne(ProductsDescription::class, ['products_id' => 'products_id'])
                ->andWhere([ProductsDescription::tableName() . '.language_id' => (int)$languages_id, ProductsDescription::tableName() . '.platform_id' => $platform_id]);
      }
    }

    public function getAnyListingName() {
      $languages_id = \Yii::$app->settings->get('languages_id');
      if ((\Yii::$container??null)&&\Yii::$container->has('_languages'))
         $languages_id=(array)(\Yii::$container->get('_languages'));
      if (!is_array($languages_id)) $languages_id=intval($languages_id);

      /**
       * @var $search \common\extensions\PlainProductsDescription\models\PlainProductsNameSearch
       * @var $toProducts \common\extensions\PlainProductsDescription\models\PlainProductsNameToProducts
       */
      $search = \common\helpers\Extensions::getModel('PlainProductsDescription', 'PlainProductsNameSearch');
      $toProducts = \common\helpers\Extensions::getModel('PlainProductsDescription', 'PlainProductsNameToProducts');

      if ( !empty($search) && !empty($toProducts) ) {

        return $this->hasOne($search, ['id' => 'plain_id'])
            ->andWhere([$search::tableName() . '.language_id' => $languages_id])
            ->viaTable($toProducts::tableName(), ['products_id' => 'products_id'])
            ;

      } else {
        return $this->hasOne(ProductsDescription::class, ['products_id' => 'products_id'])
                ->andWhere([ProductsDescription::tableName() . '.language_id' => $languages_id
                    //,        ProductsDescription::tableName() . '.platform_id' => \common\classes\platform::currentId()
                    ]);
      }
    }

    public function getPlainProductsNameToProducts() {

      /**
       * @var $toProducts \common\extensions\PlainProductsDescription\models\PlainProductsNameToProducts
       */
      $toProducts = \common\helpers\Extensions::getModel('PlainProductsDescription', 'PlainProductsNameToProducts');
      if (!empty($toProducts)) {
        return $this->hasOne($toProducts, ['products_id' => 'products_id']);
      } else {
        return $this;
      }
    }

    public function getlistingPrice() {
      if (USE_MARKET_PRICES == 'True') {
        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        $currencies_id = (int)$currencies->currencies[$currency]['id'];
      } else {
        $currencies_id = 0;
      }
      $groups_id =  (int) \Yii::$app->storage->get('customer_groups_id');

      /*
       * @var $extModel \common\extensions\ProductPriceIndex\modelsProductPriceIndex
       */
      $extModel = \common\helpers\Extensions::getModel('ProductPriceIndex', 'ProductPriceIndex');
      if (!empty($extModel)) {
        return $this->hasOne($extModel, ['products_id' => 'products_id'])
            ->andOnCondition([
              'currencies_id' => $currencies_id,
              'groups_id' => $groups_id,
              $extModel::tableName() . '.products_status' => 1
            ]);


      } else {
        return $this->hasOne(ProductsPrices::class, ['products_id' => 'products_id'])
            ->andOnCondition(
              'products_group_price <>-1'
              )
            ->andOnCondition([
              'currencies_id' => $currencies_id,
              'groups_id' => $groups_id,
            ]);
      }
    }

    public function getDeliveryTerm() {
      return $this->hasOne(ProductsStockDeliveryTerms::class, ['stock_delivery_terms_id' => 'stock_delivery_terms_id']);
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributes()
    {
        return $this->hasMany(ProductsAttributes::class, ['products_id' => 'products_id']);
    }

    public function getListingGlobalSort() {
      return $this->hasOne(ProductsGlobalSort::class, ['products_id' => 'products_id'])
          ->andOnCondition([ProductsGlobalSort::tableName().'.platform_id' => \common\classes\platform::currentId()]);
    }

    /** VL2do
     * one-to-many 1 language 1 platform (current)
     * @return \yii\db\ActiveQuery
     */
    public function getListingInventories()
    {
        return $this->hasMany(Inventory::class, ['prid' => 'products_id'])
                ->where(['non_existent' => 0]);
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getListingAttributes()
    {
        return $this->hasMany(ProductsAttributes::class, ['products_id' => 'products_id'])
            ->joinWith('productsOptions')
            ->joinWith('productsOptionsValues')
            ;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getlistingCategories()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasMany(CategoriesDescription::class, ['categories_id' => 'categories_id'])->andOnCondition(['language_id' => $languages_id])
            ->viaTable(Products2Categories::tableName(), ['products_id' => 'products_id'])
            ->innerJoin(TABLE_CATEGORIES, TABLE_CATEGORIES.'.categories_id=' . CategoriesDescription::tableName() . '.categories_id and categories_status = 1')
            ->select('categories_name')
            ->addSelect(CategoriesDescription::tableName() . '.categories_id, language_id')
            ->addSelect('parent_id, categories_status')
            ;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getListingProperties()
    {
        return $this->hasMany(Properties::class, ['properties_id' => 'properties_id'])->andOnCondition(['display_listing' => 1])
            ->viaTable(Properties2Propducts::tableName(), ['products_id' => 'products_id'])
            ;
    }
//
    /**
     * one-to-many all languages all platforms
     * @return \yii\db\ActiveQuery
     */
    public function getSearchDescriptions()
    {
        return $this->hasMany(ProductsDescription::class, ['products_id' => 'products_id'])->select(self::$searchDescriptionFields)
            ->addSelect('products_id, language_id, platform_id, department_id')
            ->indexBy(function ($row) { return $row['language_id'] . '_' . $row['department_id'] . '_' . $row['platform_id'] ;} )
            ;
    }

    /**
     * one-to-many 1 language 1 platform (current)
     * @return \yii\db\ActiveQuery
     */
    public function getSearchAttributes()
    {
        return $this->hasMany(ProductsAttributes::class, ['products_id' => 'products_id'])->with(['searchProductsOptions', 'searchProductsOptionsValues'])
            ->select('products_id, options_id, options_values_id, products_options_sort_order, products_attributes_filename')
            ;
    }


    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getSearchProperties()
    {
        return $this->hasMany(Properties2Propducts::class, ['products_id' => 'products_id'],
                function ($query) {
                    $query->onCondition('values_flag is null or values_flag = 1')
                          ->joinWith('properties')->andOnCondition(['display_search' => 1])
                        ;
                })
            ->with(['searchDescriptions', 'searchValues'])
            ;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Properties2Propducts::class, ['products_id' => 'products_id'],
                function ($query) {
                    $query->onCondition('values_flag is null or values_flag = 1')
                          ->joinWith('properties')
                        ;
                })
            ->with(['propertiesValue'])
            ;
    }

    public function getProperties2Products()
    {
        return $this->hasMany(Properties2Propducts::class, ['products_id' => 'products_id']);
    }

// use if ($ext=\common\helpers\Extensions::isAllowed('GoogleAnalyticsTools')) $ext::attachJoin($productsQuery)
//    public function getSearchGapi()
//    {
//        /**
//         * @var $ext \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools
//         */
//        if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
//            return $ext::productRelation($this);
//        }
//        return $this;
//    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getSearchInventories()
    {
        return $this->hasMany(Inventory::class, ['prid' => 'products_id'])
                    ->andOnCondition(['non_existent' => 0])
                    ->select(self::$searchInventoryFields)
                    ->addSelect('products_id, prid')
                    ->indexBy('products_id')
            ;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getSearchCategories()
    {
        return $this->hasMany(CategoriesDescription::class, ['categories_id' => 'categories_id'])
            ->viaTable(Products2Categories::tableName(), ['products_id' => 'products_id'])
            ->select('categories_name')
            ->addSelect('categories_id, language_id')
            ;
    }

    /**
     * one-to-one 1 language 1 platform (current)
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultImage()
    {
        return $this->hasOne(ProductsImages::class, ['products_id' => 'products_id'])
                                           ->where(['default_image' => 1,
                                                    'image_status' => 1]);
    }

    /**
     * @deprecated shit
     * one-to-one
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(PlatformsProducts::class, ['products_id' => 'products_id']);
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getVisiblePlatforms()
    {
      $plids =  \common\classes\platform::getList();
      $plids = \yii\helpers\ArrayHelper::getColumn($plids, 'id');

        return $this->hasMany(PlatformsProducts::class, ['products_id' => 'products_id'])->andOnCondition(['platform_id' => $plids])->indexBy('platform_id'); //indexby important
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getPlatforms()
    {
        return $this->hasMany(PlatformsProducts::class, ['products_id' => 'products_id'])->indexBy('platform_id'); //indexby important
    }

    public function getReviews()
    {
      return $this->hasMany(\common\models\Reviews::class, ['products_id' => 'products_id']);
    }

    /**
     * one-to-one
     * @return \yii\db\ActiveQuery
     */
    public function getLocalRating()
    {
      $ret = $this->getReviews()->select(['products_id' => 'products_id', 'average_rating' => new \yii\db\Expression('avg(reviews_rating) / 5 * 100') ]);

      return $ret;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getCategoriesList()
    {
        return $this->hasMany(Products2Categories::class, ['products_id' => 'products_id']);
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Categories::class, ['categories_id' => 'categories_id'])->viaTable(Products2Categories::tableName(), ['products_id' => 'products_id']);
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getProductImagesList()
    {
        return $this->hasMany(ProductsImages::className(), ['products_id' => 'products_id']);
    }

    public function getProductsPrices()
    {
        return $this->hasMany(ProductsPrices::className(), ['products_id' => 'products_id']);
    }

    public function getProductsAttributes()
    {
        return $this->hasMany(ProductsAttributes::className(), ['products_id' => 'products_id']);
    }

    public function getGiftWrap()
    {
        return $this->hasMany(ProductsGiftWrap::className(), ['products_id' => 'products_id']);
    }

    public function getProductsFeatured()
    {
        return $this->hasMany(ProductsFeatured::className(), ['products_id' => 'products_id']);
    }

    public function getProductsVideos()
    {
        return $this->hasMany(ProductsVideos::className(), ['products_id' => 'products_id']);
    }

// SeoRedirectsNamed model moved to extensions/SeoRedirectsNamed/models
//    public function getSeoRedirectsNamed()
//    {
//        return $this->hasMany(SeoRedirectsNamed::className(), ['owner_id' => 'products_id'])->andWhere(['redirects_type'=>'product']);
//    }

    /**
     * one-to-many
     * @return array
     */
    public function getInventories()
    {
        return $this->hasMany(Inventory::class, ['prid' => 'products_id']);
    }

    public function getTaxClass()
    {
        return $this->hasOne(TaxClass::class, ['tax_class_id' => 'products_tax_class_id']);
    }

    public function getTaxRate()
    {
        return $this->hasOne(TaxRates::class, ['tax_class_id' => 'tax_class_id'])->via('taxClass');
    }

    public static function find()
    {
        return new ProductsQuery(get_called_class());
    }

    public function getSuppliersProducts()
    {
        return $this->hasMany(SuppliersProducts::class, ['products_id' => 'products_id']);
    }

    /**
     *
     * @param integer $excludeSupplier
     * @return activeQuery
     */
    public function getActiveSuppliersProducts($excludeSupplier=false) {
      $ret = $this->getSuppliersProducts()->andWhere(['status' => 1]);
      if ( $excludeSupplier ) {
        $ret->andWhere('suppliers_id <> :suppliers_id', [':suppliers_id' => $excludeSupplier]);
      }
      return $ret;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    /*
      public function getPrices($customer_groups_id = 0, $currency_id = 0)
      {
      return $this->hasOne(ProductsPrices::class, ['products_id' => 'products_id'])->where('group1s_id =:groups_id', [':groups_id' => $customer_groups_id])->andWhere('currencies_id = :currencies_id', [':currencies_id' => $currency_id]);
      } */

    // public function getGroupsProducts() - removed due extracting extension UsersGroupsRestriction - use this:
    // if ($model = Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsProducts')) {
    //    $yourModel->innerJoin($model::tableName() ...)

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        \Yii::warning('Using UserGroupRestrictions table. Not recommended - the table may be not exist in some osCommerce versions');
        return $this->hasMany(Groups::class, ['groups_id' => 'groups_id'])->viaTable('groups_products', ['products_id' => 'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManufacturer()
    {
        return $this->hasOne(Manufacturers::class, ['manufacturers_id' => 'manufacturers_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubProducts()
    {
        return $this->hasMany(Products::class, ['parent_products_id' => 'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubProductParent()
    {
        return $this->hasOne(Products::class, ['products_id' => 'parent_products_id']);
    }

    public function getSet(){
        return $this->hasMany(SetsProducts::class,['sets_id' => 'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDepartments()
    {
        return $this->hasMany(DepartmentsProducts::class, ['products_id' => 'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsNotes()
    {
        return $this->hasMany(ProductsNotes::class, ['products_id' => 'products_id']);
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        if ( (int)$this->products_id>0 ) {
            foreach (\common\models\Products::find()
                         ->where(['parent_products_id' => (int)$this->products_id])
                         ->asArray()
                         ->all() as $sub_product) {
                //$sub_product->delete();
                \common\helpers\Product::remove_product($sub_product->products_id);
            }
        }

        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ( $insert ){
            if ($this->parent_products_id){
                $this->products_id_stock = $this->parent_products_id;
                $this->products_id_price = $this->parent_products_id;
                $this->_recalculate_sub_product_parent_id = $this->parent_products_id;
            }
            if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS'))>0 && empty($this->products_new_until)) {
              $this->products_new_until = date(\common\helpers\Date::DATABASE_DATE_FORMAT, strtotime('+' . intval(constant('NEW_MARK_UNTIL_DAYS')) . ' day') );
            }
        }else{
            if ( $this->isAttributeChanged('parent_products_id',false) ){
                $this->products_id_stock = $this->parent_products_id?intval($this->parent_products_id):$this->products_id;
                $this->products_id_price = $this->parent_products_id && !\common\helpers\Product::isSubProductWithPrice()?intval($this->parent_products_id):$this->products_id;
                // for recalculate ref count on parent if parent changed to 0
                if ( intval($this->parent_products_id)==0 ){
                    $this->_recalculate_sub_product_parent_id = intval($this->getOldAttribute('parent_products_id'));
                }else{
                    $this->_recalculate_sub_product_parent_id = intval($this->parent_products_id);
                }
            }
            static::updateAll(['products_price_full'=>$this->products_price_full],['parent_products_id'=>$this->products_id, 'products_id_price'=>$this->products_id]);
        }

        if ( $insert ) {
            foreach ($this->getTableSchema()->columns as $column) {
                /**
                 * @var $column ColumnSchema
                 */
                if (!$column->allowNull && ($this->getAttribute($column->name) === null || $column->dbTypecast($this->getAttribute($column->name))===null) ) {
                    $defValue = $column->defaultValue;
                    if ( $column->dbTypecast($defValue)===null ) {
                        $defTypeValue = [
                            'boolean' => 0,
                            'float' => 0.0,
                            'decimal' => 0.0,
                        ];
                        if ( stripos($column->type,'int')!==false ) {
                            $defValue = 0;
                        }else{
                            $defValue = isset($defTypeValue[$column->type])?$defTypeValue[$column->type]:'';
                        }
                    }
                    $this->setAttribute($column->name, $defValue);
                }
            }
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( !$this->parent_products_id ) {
            if ( $insert ) {
                // parented product handled in before save
                static::updateAll(
                    [
                        'products_id_stock' => $this->parent_products_id ? intval($this->parent_products_id) : intval($this->products_id),
                        'products_id_price' => $this->parent_products_id && !\common\helpers\Product::isSubProductWithPrice() ? intval($this->parent_products_id) : intval($this->products_id),
                    ],
                    ['products_id' => intval($this->products_id)]
                );
            }
        }
        if (empty($this->parent_products_id) && array_key_exists('products_status', $changedAttributes)){
            if ( $this->getAttribute('products_status') ) {
                static::updateAll(
                    [
                        'products_status' => new Expression('IFNULL(sub_product_prev_status,1)'),
                        'sub_product_prev_status' => new Expression('NULL'),
                        'products_last_modified' => new Expression('NOW()'),
                    ],
                    ['parent_products_id' => intval($this->products_id)]
                );
            }else {
                static::updateAll(
                    [
                        'sub_product_prev_status' => new Expression('products_status'),
                        'products_status' => 0,
                        'products_last_modified' => new Expression('NOW()'),
                    ],
                    ['parent_products_id' => intval($this->products_id)]
                );
            }
        }
        if ($this->_recalculate_sub_product_parent_id) {
            $childCount = static::find()
                ->where(['parent_products_id' => intval($this->_recalculate_sub_product_parent_id)])
                ->count();
            static::updateAll(
                ['sub_product_children_count'=>(int)$childCount],
                ['products_id'=>intval($this->_recalculate_sub_product_parent_id)]
            );
            $this->_recalculate_sub_product_parent_id = null;
        }

        if ( $insert ) {
            /** @var \common\extensions\UserGroupsRestrictions\UserGroupsRestrictions $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                if ( $groupService = $ext::getGroupsService() ){
                    $groupService->addProductToAllGroups($this->products_id);
                }
            }
        }
        if ( array_key_exists('products_groups_id', $changedAttributes) ) {
            \common\helpers\ProductsGroupSortCache::update($this->products_id);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ( $this->parent_products_id ) {
            $childCount = static::find()
                ->where(['parent_products_id' => intval($this->parent_products_id)])
                ->count();
            static::updateAll(
                ['sub_product_children_count'=>(int)$childCount],
                ['products_id'=>intval($this->parent_products_id)]
            );
        }else{
            foreach (static::find()
                ->where(['parent_products_id'=>$this->products_id])
                ->select(['products_id'])
                ->asArray()
                ->all() as $childProduct){
                \common\helpers\Product::remove_product($childProduct['products_id']);
            }
        }
    }

    public function getBackendDescription() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      if (\backend\models\ProductNameDecorator::instance()->useInternalNameForListing()) {
        $nameColumn = new \yii\db\Expression("IF(LENGTH(" . ProductsDescription::tableName() . ".products_internal_name), " . ProductsDescription::tableName() . ".products_internal_name, " . ProductsDescription::tableName() . ".products_name)");
      } else {
        $nameColumn = ProductsDescription::tableName() . '.products_name';
      }

      return $this->hasOne(\common\models\ProductsDescription::class, ['products_id' => 'products_id'])
                ->select(['products_name' => $nameColumn])
                ->addSelect(['platform_id', 'products_id', 'language_id'])
                ->andOnCondition(['language_id' => (int)$languages_id,
                                  'platform_id' => intval(\common\classes\platform::defaultId())
                  ])
                //->orderBy($nameColumn)
                ;
    }

// SeoRedirectsNamed model moved to extensions/SeoRedirectsNamed/models
//    public function getSeoRedirects() {
//       return $this->hasMany(SeoRedirectsNamed::class, ['owner_id' => 'products_id'])->andOnCondition('redirects_type = "product"');
//    }

    // incapsulate product types for hooks
    public function returnProductType()
    {
        return ($this->parent_products_id) ? 'subproduct' : 'product';
    }

}
