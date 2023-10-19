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

class SaveSubProductMainDetails
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function prepareSave()
    {
        $parentModel = \common\models\Products::findOne($this->product->parent_products_id);
        $parent_data = $parentModel->getAttributes();
        $sql_data_array = [];
        // {{
        $sql_data_array['products_tax_class_id'] = $parent_data['products_tax_class_id'];
        // }}

        $sql_data_array['products_status'] = (int) Yii::$app->request->post('products_status');
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')) {
            $_sql_data = $ext::onProductSave();
            if ( is_array($_sql_data) ) $sql_data_array = array_merge($sql_data_array,$_sql_data);
        }
        //TODO: ???? separate prepare - ACL collision
        // Moved to SeoRedirectsNamed
        // $sql_data_array['products_old_seo_page_name'] = tep_db_prepare_input($_POST['pDescription'][\common\classes\platform::defaultId()]['products_old_seo_page_name']);

        $sql_data_array['manufacturers_id'] = $parent_data['manufacturers_id'];
        $sql_data_array['stock_indication_id'] = $parent_data['stock_indication_id'];
        $sql_data_array['stock_delivery_terms_id'] = $parent_data['stock_delivery_terms_id'];

        $sql_data_array['products_model'] = Yii::$app->request->post('products_model');
        $sql_data_array['products_ean'] = Yii::$app->request->post('products_ean');
        $sql_data_array['products_upc'] = Yii::$app->request->post('products_upc');
        $sql_data_array['products_asin'] = Yii::$app->request->post('products_asin');
        $sql_data_array['products_isbn'] = Yii::$app->request->post('products_isbn');


        $sql_data_array['subscription'] = (int) Yii::$app->request->post('subscription');
        $sql_data_array['subscription_code'] = Yii::$app->request->post('subscription_code');

        // save product behavior buttons
        $sql_data_array['request_quote'] = $parent_data['request_quote'];
        $sql_data_array['request_quote_out_stock'] = $parent_data['request_quote_out_stock'];
        $sql_data_array['ask_sample'] = $parent_data['ask_sample'];
        $sql_data_array['allow_backorder'] = $parent_data['allow_backorder'];
        $sql_data_array['cart_button'] = $parent_data['cart_button'];

        $sql_data_array['manual_stock_unlimited'] = $parent_data['manual_stock_unlimited'];

        $products_date_available = Yii::$app->request->post('products_date_available');
        if (!empty($products_date_available)) {
            $sql_data_array['products_date_available'] = \common\helpers\Date::prepareInputDate($products_date_available);
        } else {
            $sql_data_array['products_date_available'] = '';
        }

        $sql_data_array['products_pctemplates_id'] = $parent_data['products_pctemplates_id'];

        // product designer template
        $sql_data_array['product_designer_template_id'] = $parent_data['product_designer_template_id'];

        $sql_data_array['stock_control'] = $parent_data['stock_control'];
        $sql_data_array['stock_reorder_level'] = $parent_data['stock_reorder_level'];
        $sql_data_array['stock_reorder_quantity'] = $parent_data['stock_reorder_quantity'];
        
        $sql_data_array['stock_limit'] = $parent_data['stock_limit'];

        $this->product->setAttributes($sql_data_array, false);
    }

}