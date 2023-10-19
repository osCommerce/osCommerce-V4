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

namespace backend\models\ProductEdit;

use yii;
use common\models\Products;

class SaveMainDetails
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function prepareSave()
    {
        $sql_data_array = [];

        if (Yii::$app->request->post('without_inventory', false)!==false){
            $sql_data_array['without_inventory'] = (int) Yii::$app->request->post('without_inventory',0);
        } elseif (!\common\helpers\Extensions::isAllowed('Inventory')) {
            $sql_data_array['without_inventory'] = 1;
        }

        if (Yii::$app->request->post('listing_switch_present')){
            $sql_data_array['is_listing_product'] = (int) Yii::$app->request->post('is_listing_product',0);
        }
        $sql_data_array['products_status'] = (int) Yii::$app->request->post('products_status');
        //$sql_data_array['products_status_bundle'] = (int) Yii::$app->request->post('products_status_bundle');
        /** @var \common\extensions\AutomaticallyStatus\AutomaticallyStatus $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('AutomaticallyStatus')) {
            $_sql_data = $ext::onProductSave();
            if ( is_array($_sql_data) ) $sql_data_array = array_merge($sql_data_array,$_sql_data);
        }
        //TODO: ???? separate prepare - ACL collision
        // Moved to SeoRedirectsNamed
        // $sql_data_array['products_old_seo_page_name'] = tep_db_prepare_input($_POST['pDescription'][\common\classes\platform::defaultId()]['products_old_seo_page_name']);

        $sql_data_array['manufacturers_id'] = (int) Yii::$app->request->post('manufacturers_id');
        $brandName = tep_db_prepare_input(Yii::$app->request->post('brand'));
        if (empty($brandName)) {
            $sql_data_array['manufacturers_id'] = 0;
        } else {
            $brands_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_name = '" . tep_db_input($brandName) . "'");
            $brands = tep_db_fetch_array($brands_query);
            if (isset($brands['manufacturers_id'])) {
                $sql_data_array['manufacturers_id'] = (int) $brands['manufacturers_id'];
            }
        }
        $sql_data_array['stock_indication_id'] = (int)Yii::$app->request->post('stock_indication_id', 0);

        $sql_data_array['stock_delivery_terms_id'] = (int)Yii::$app->request->post('stock_delivery_terms_id',0);

        $sql_data_array['products_model'] = Yii::$app->request->post('products_model');
        $sql_data_array['products_ean'] = Yii::$app->request->post('products_ean');
        $sql_data_array['products_upc'] = Yii::$app->request->post('products_upc');
        $sql_data_array['products_asin'] = Yii::$app->request->post('products_asin');
        $sql_data_array['products_isbn'] = Yii::$app->request->post('products_isbn');

        $sql_data_array['source'] = Yii::$app->request->post('source');

        $sql_data_array['subscription'] = (int) Yii::$app->request->post('subscription');
        $sql_data_array['subscription_code'] = Yii::$app->request->post('subscription_code');

        // save product behavior buttons
        $sql_data_array['request_quote'] = Yii::$app->request->post('request_quote', 0);
        $sql_data_array['request_quote_out_stock'] = Yii::$app->request->post('request_quote_out_stock', 0);
        $sql_data_array['ask_sample'] = Yii::$app->request->post('ask_sample', 0);
        $sql_data_array['allow_backorder'] = Yii::$app->request->post('allow_backorder', 0);
        $sql_data_array['cart_button'] = Yii::$app->request->post('cart_button');

        $sql_data_array['reorder_auto'] = Yii::$app->request->post('reorder_auto', 0);

        $sql_data_array['manual_stock_unlimited'] = (int)Yii::$app->request->post('manual_stock_unlimited', 0);

        $sql_data_array['product_unit_label'] = \common\helpers\Product::getUnitLabelList(true, Yii::$app->request->post('product_unit_label', ''), false);

        $products_date_available = Yii::$app->request->post('products_date_available');
        if (!empty($products_date_available)) {
            $sql_data_array['products_date_available'] = \common\helpers\Date::prepareInputDate($products_date_available);
        } else {
            $sql_data_array['products_date_available'] = '';
        }

        $products_new_until = Yii::$app->request->post('products_new_until');
        if (!empty($products_new_until)) {
            $sql_data_array['products_new_until'] = \common\helpers\Date::prepareInputDate($products_new_until);
        } else {
            $sql_data_array['products_new_until'] = '';
        }

        $sql_data_array['products_pctemplates_id'] = Yii::$app->request->post('products_pctemplates_id');

        // product designer template
        $sql_data_array['product_designer_template_id'] = Yii::$app->request->post('product_designer_template_id');

        $this->product->setAttributes(['stock_control' => 0], false);

        $sql_data_array['stock_reorder_level'] = (int)Yii::$app->request->post('stock_reorder_level', -1);
        $sql_data_array['stock_reorder_quantity'] = (int)Yii::$app->request->post('stock_reorder_quantity', -1);

        $sql_data_array['stock_limit'] = (int)Yii::$app->request->post('stock_limit', -1);

        $sql_data_array['jsonld_product_type'] = (int)Yii::$app->request->post('jsonld_product_type', 0);

        $sql_data_array['out_stock_action'] = (int)Yii::$app->request->post('out_stock_action', 0);
        $sql_data_array['is_demo'] = (int)Yii::$app->request->post('is_demo', 0);

        $this->product->setAttributes($sql_data_array, false);
    }

}