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

namespace backend\models\EP\Provider;

use backend\models\EP\Formatter;
use backend\models\EP;
use backend\models\EP\Messages;
use common\helpers\Html;
use yii\base\Exception;
use yii\db\Expression;

class Products extends ProviderAbstract implements ImportInterface, ExportInterface {

    protected $entry_counter = 0;
    protected $max_categories = 7;
    protected $fields = array();
    protected $additional_fields = array();
    protected $fields_languages = array();

    protected $additional_data = array();

    protected $data = array();
    protected $EPtools;
    protected $export_query;
    protected $enough_columns_for_create = null;
    protected $makeNetPricesOnImport = null;
    protected $exportPriceGross = false;

    protected $stock_warning = null;

    function init()
    {
        parent::init();
        $this->initFields();
        $this->initAdditionalFields();

        $this->EPtools = new EP\Tools();
        $this->enough_columns_for_create = null;
    }

    public function importOptions()
    {

        $insert_new = isset($this->import_config['insert_new'])?$this->import_config['insert_new']:'insert';
        $new_seo_name = isset($this->import_config['new_seo_name'])?$this->import_config['new_seo_name']:'generate';
        $imported_price_is_gross = isset($this->import_config['imported_price_is_gross'])?$this->import_config['imported_price_is_gross']:'no';

        return '
<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>Import options</h4></div>
    <div class="widget-content">
        <div class="row form-group">
            <div class="col-md-6"><label>Insert new</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[insert_new]',$insert_new, ['insert'=>'Yes, insert new','update'=>'No, just update existing'],['class'=>'form-control']).'</div>
        </div>
        <div class="row">
            <div class="col-md-6"><label>New SEO page name</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[new_seo_name]',$new_seo_name, ['generate'=>'Yes, Generate new','keep'=>'No, keep seo page name as is'],['class'=>'form-control']).'</div>
        </div>
        <div class="row">
            <div class="col-md-6"><label>Imported Prices with tax?</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[imported_price_is_gross]',$imported_price_is_gross, ['no'=>'No, Net Price','yes'=>'Yes, price contain TAX value'],['class'=>'form-control']).'</div>
        </div>
    </div>
</div>
        ';
    }

    protected function initFields()
    {
        \common\helpers\Translation::init('admin/categories');

        $currencies = \Yii::$container->get('currencies');

        //$this->fields[] = array('name' => 'products_tax_class_id', 'value' => 'Products Tax Class possible values: ' . $txt);
        $checkUniqKey = \Yii::$app->getDb()->createCommand("select count(*) as c from products group by products_model having count(*)>1")->queryScalar();
        if ( $checkUniqKey ){
            $this->fields[] = array( 'name' => 'products_id', 'value' => 'Products Id', 'is_key'=>true, 'is_key_part'=>true );
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key_part'=>true );
        }else{
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key'=>true );
        }
        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
            $this->fields[] = array('name' => 'is_listing_product', 'value' => 'Listing product?');
            $this->fields[] = array('name' => 'parent_products_id', 'value' => 'Parent Model', 'set'=>'set_parent_product_model', 'get'=>'get_parent_product_model');
            //$this->fields[] = array('name' => '_products_id_price', 'value' => 'Child With Own Price?', 'set'=>'set_child_with_own_price', 'get'=>'get_child_with_own_price');
        }
        $this->fields[] = array( 'name' => 'products_quantity', 'value' => 'Products Quantity', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'sort_order', 'value' => 'Products Sort order' );
        $this->fields[] = array( 'name' => 'products_date_available', 'value' => 'Products Date Available', 'type' => 'date', 'set'=>'set_date', );
        $this->fields[] = array( 'name' => 'products_date_added', 'value' => 'Products Date Added', 'type' => 'date', 'set'=>'set_date', );
        $this->fields[] = array( 'name' => 'products_new_until', 'value' => 'Mark new until', 'type' => 'date', 'set'=>'set_date', );
        $this->fields[] = array( 'name' => 'weight_cm', 'value' => 'Products Weight', 'type' => 'numeric' );

        if (defined('SHOW_EAN') && SHOW_EAN=='True') {
            $this->fields[] = array('name' => 'products_ean', 'value' => TEXT_EAN);
        }
        if (defined('SHOW_UPC') && SHOW_UPC=='True') {
            $this->fields[] = array('name' => 'products_upc', 'value' => TEXT_UPC);
        }
        if (defined('SHOW_ASIN') && SHOW_ASIN=='True') {
            $this->fields[] = array('name' => 'products_asin', 'value' => TEXT_ASIN);
        }
        if (defined('SHOW_ISBN') && SHOW_ISBN=='True') {
                $this->fields[] = array('name' => 'products_isbn', 'value' => TEXT_ISBN);
        }
        if (\common\helpers\Extensions::isAllowed('TypicalOperatingTemp')) {
            $this->fields[] = array( 'name' => 'TOTemperature', 'value' => 'TO Temperature' );
        }

        $this->fields[] = array( 'name' => 'stock_limit', 'value' => 'Stock limit', 'type' => 'int' );
        
        $this->fields[] = array( 'name' => 'order_quantity_minimal', 'value' => 'Order quantity minimal', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'order_quantity_max', 'value' => 'Order quantity max', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'order_quantity_step', 'value' => 'Order quantity step', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'item_in_virtual_item_qty', 'value' => 'Items in virtual item', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'item_in_virtual_item_step', 'value' => 'Virtual item step' );
        $this->fields[] = array( 'name' => 'product_unit_label', 'value' => 'Unit label code' );


        $this->fields[] = array( 'name' => 'source', 'value' => 'Source');
        $this->fields[] = array( 'name' => 'products_status', 'value' => 'Products Status', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'request_quote', 'value' => 'Request for quote', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'request_quote_out_stock', 'value' => 'Allow quote out of stock', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'ask_sample', 'value' => 'Request for sample', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'cart_button', 'value' => 'Add to cart button', 'type' => 'int' );

        $this->fields[] = array( 'name' => 'manufacturers_id', 'value' => 'Brand', 'set'=>'set_brand', 'get'=>'get_brand' );

        $this->fields[] = array( 'name' => 'products_pctemplates_id', 'value' => 'Customisation Template ', 'set'=>'set_pctemplate', 'get'=>'get_pctemplate' );

        $this->fields[] = array( 'name' => 'products_price_rrp', 'value' => 'RRP', 'type' => 'numeric' );
        
        $this->fields[] = array( 'name' => 'is_demo', 'value' => 'Demo product', 'type' => 'int' );
// {{
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        $txt = '';
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $txt .= $tax_class['tax_class_id'] . '=' . $tax_class['tax_class_title'] . ';';
        }
        $this->fields[] = array('name' => 'products_tax_class_id', 'value' => 'Products Tax Class', 'set'=>'set_tax_class', 'get'=>'get_tax_class');
        $this->fields[] = array('name' => 'products_file', 'value' => 'Product File');

        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {

                $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|'.$value['id'].'|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_price',
                    'name' => 'products_price_' . $value['id'] . '_0',
                    'value' => 'Products Price ' . $key,
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_discount_price',
                    'name' => 'products_price_discount_' . $value['id'] . '_0',
                    'value' => 'Products Discount Price ' . $key . ' ',
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'bonus_points_price',
                    'name' => 'bonus_points_price_' . $value['id'] . '_0',
                    'value' => 'Bonus point price ' . $key . ' ',
                    //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'set' => 'set_bonus_point_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'bonus_points_cost',
                    'name' => 'bonus_points_cost_' . $value['id'] . '_0',
                    'value' => 'Bonus point cost ' . $key . ' ',
                    //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'set' => 'set_bonus_point_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'products_group_price',
                        'name' => 'products_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Products Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_price', 'set' => 'set_products_price',
                        'type' => 'numeric'
                    );

                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'products_group_discount_price',
                        'name' => 'products_price_discount_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Products Discount Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                        'type' => 'price_table'
                    );
                    $this->fields[] = array(
                         'data_descriptor' => $data_descriptor,
                         'column_db' => 'bonus_points_price',
                         'name' => 'bonus_points_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                         'value' => 'Bonus point price ' . $key . ' ' . $groups_data['groups_name'],
                         //'get' => 'get_products_bonus_price', 'set' => 'set_products_bonus_price',
                         'type' => 'numeric'
                     );
                     $this->fields[] = array(
                         'data_descriptor' => $data_descriptor,
                         'column_db' => 'bonus_points_cost',
                         'name' => 'bonus_points_cost_' . $value['id'] . '_' . $groups_data['groups_id'],
                         'value' => 'Bonus point cost ' . $key . ' ' . $groups_data['groups_name'],
                         //'get' => 'get_products_bonus_cost', 'set' => 'set_products_bonus_cost',
                         'type' => 'numeric'
                     );

                }

            }
        } else {
            $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|0';
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_price',
                //'column_db' => 'products_price',
                'name' => 'products_price_0',
                'value' => 'Products Price',
                'get' => 'get_products_price', 'set' => 'set_products_price',
                //'set' => 'set_main_products_price',
                'type' => 'numeric',
                'isMain' => true,
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_discount_price',
                //'column_db' => 'products_price_discount',
                'name' => 'products_price_discount_0',
                'value' => 'Products Discount Price',
                'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                //'set' => 'set_products_discount_price',
                'type' => 'price_table',
                'isMain' => true,
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                //'column_db' => 'products_group_discount_price',
                'column_db' => 'bonus_points_price',
                'name' => 'bonus_points_price_0',
                'value' => 'Bonus point price',
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'set' => 'set_bonus_point_price',
                'type' => 'numeric',
                'isMain' => true,
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                //'column_db' => 'products_group_discount_price',
                'column_db' => 'bonus_points_cost',
                'name' => 'bonus_points_cost_0',
                'value' => 'Bonus point cost',
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'set' => 'set_bonus_point_price',
                'type' => 'numeric',
                'isMain' => true,
            );

            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_price',
                    'name' => 'products_price_' . $groups_data['groups_id'],
                    'value' => 'Products Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_group_discount_price',
                    'name' => 'products_price_discount_' . $groups_data['groups_id'],
                    'value' => 'Products Discount Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table'
                );
                $this->fields[] = array(
                     'data_descriptor' => $data_descriptor,
                     'column_db' => 'bonus_points_price',
                     'name' => 'bonus_points_price_' . $groups_data['groups_id'],
                     'value' => 'Bonus point price ' . $groups_data['groups_name'],
                     //'get' => 'get_products_bonus_price', 'set' => 'set_products_bonus_price',
                     'type' => 'numeric'
                 );
                 $this->fields[] = array(
                     'data_descriptor' => $data_descriptor,
                     'column_db' => 'bonus_points_cost',
                     'name' => 'bonus_points_cost_' . $groups_data['groups_id'],
                     'value' => 'Bonus point cost ' . $groups_data['groups_name'],
                     //'get' => 'get_products_bonus_cost', 'set' => 'set_products_bonus_cost',
                     'type' => 'numeric'
                 );
            }
        }

        // {{ gift wrap
        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {
                $data_descriptor = '%|'.TABLE_GIFT_WRAP_PRODUCTS.'|'.$value['id'].'|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'gift_wrap_price',
                    'name' => 'gift_wrap_price_' . $value['id'] . '_0',
                    'value' => 'Gift Wrap Price ' . $key,
                    'get' => 'get_gift_wrap_price', 'set' => 'set_gift_wrap_price',
                    'type' => 'numeric',
                    'key_columns' => ['currencies_id'=>$value['id'], 'groups_id' => 0],
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_GIFT_WRAP_PRODUCTS.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'gift_wrap_price',
                        'name' => 'gift_wrap_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Gift Wrap Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_gift_wrap_price', 'set' => 'set_gift_wrap_price',
                        'type' => 'numeric',
                        'key_columns' => ['currencies_id'=>$value['id'], 'groups_id' => $groups_data['groups_id']],
                    );

                }

            }
        } else {
            $data_descriptor = '%|'.TABLE_GIFT_WRAP_PRODUCTS.'|0|0';
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'gift_wrap_price',
                'name' => 'gift_wrap_price_0',
                'value' => 'Gift Wrap Price',
                'get' => 'get_gift_wrap_price', 'set' => 'set_gift_wrap_price',
                'type' => 'numeric',
                'key_columns' => ['currencies_id'=>0, 'groups_id' => 0],
                'isMain' => true,
            );

            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_GIFT_WRAP_PRODUCTS.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'gift_wrap_price',
                    'name' => 'gift_wrap_price_' . $groups_data['groups_id'],
                    'value' => 'Gift Wrap Price ' . $groups_data['groups_name'],
                    'get' => 'get_gift_wrap_price', 'set' => 'set_gift_wrap_price',
                    'key_columns' => ['currencies_id'=>0, 'groups_id' => $groups_data['groups_id']],
                    'type' => 'numeric'
                );
            }
        }
        // }} gift wrap

        $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();

        $hiddenLangs = \common\helpers\Language::getAdminHiddenLanguages();

        foreach($platforms as $platform){
          foreach( \common\helpers\Language::get_languages() as $_lang ) {
                $platform_postfix = '_'.$platform->platform_id;
                $platform_name_postfix = ($platform->is_default?'':' on '.$platform->platform_name);
                $data_descriptor = '%|'.TABLE_PRODUCTS_DESCRIPTION.'|'.$_lang['id'].'|'.$platform->platform_id;
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_name',
                    'name' => 'products_name_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Name '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_internal_name',
                    'name' => 'products_internal_name_'.$_lang['code'].$platform_postfix,
                    'value' => 'Internal Name '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_description_short',
                    'name' => 'products_description_short_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Short Description '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_description',
                    'name' => 'products_description_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Description '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_url',
                    'name' => 'products_url_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products URL '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_head_title_tag',
                    'name' => 'products_head_title_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Head Title Tag '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_head_desc_tag',
                    'name' => 'products_head_desc_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Description Tag '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_head_keywords_tag',
                    'name' => 'products_head_keywords_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products Keywords Tag '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_self_service',
                    'name' => 'products_self_service_'.$_lang['code'].$platform_postfix,
                    'value' => 'Self-service metadata '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_seo_page_name',
                    'name' => 'products_seo_page_name_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products SEO page name '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_h1_tag',
                    'name' => 'products_h1_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products H1 tag '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_h2_tag',
                    'name' => 'products_h2_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products H2 tags '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'products_h3_tag',
                    'name' => 'products_h3_tag_'.$_lang['code'].$platform_postfix,
                    'value' => 'Products H3 tags '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'rel_canonical',
                    'name' => 'rel_canonical_'.$_lang['code'].$platform_postfix,
                    'value' => 'Canonical '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'noindex_option',
                    'name' => 'noindex_option_'.$_lang['code'].$platform_postfix,
                    'value' => 'META noindex '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'nofollow_option',
                    'name' => 'nofollow_option_'.$_lang['code'].$platform_postfix,
                    'value' => 'META nofollow '.$_lang['code'] . $platform_name_postfix,
                    'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
                );

            }
        }

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
          /// products groups
          $data_descriptor = '@|assigned_products_groups|'.$_lang['id'];
          $this->fields[] = array(
              'data_descriptor' => $data_descriptor,
              'name' => 'products_groups_name_'.$_lang['id'],
              'value' => 'Products Group Name '.$_lang['code'],
              'set' => 'set_products_groups',
              'get' => 'get_products_groups',
              'adm_export_hidden' => in_array($_lang['id'], $hiddenLangs),
          );
        }

        $this->fields[] = array( 'name' => 'is_virtual', 'value' => 'Is Virtual?', 'type' => 'int' );

        $this->fields[] = array( 'name' => 'dimensions_cm', 'value' => 'Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'length_cm', 'value' => 'Length (cm)');
        $this->fields[] = array( 'name' => 'width_cm', 'value' => 'Width (cm)');
        $this->fields[] = array( 'name' => 'height_cm', 'value' => 'Height (cm)');

        $this->fields[] = array( 'name' => 'inner_carton_dimensions_cm', 'value' => 'Pack Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'inner_length_cm', 'value' => 'Pack Length (cm)');
        $this->fields[] = array( 'name' => 'inner_width_cm', 'value' => 'Pack Width (cm)');
        $this->fields[] = array( 'name' => 'inner_height_cm', 'value' => 'Pack Height (cm)');
        $this->fields[] = array( 'name' => 'inner_weight_cm', 'value' => 'Pack Weight (kg)');
        $this->fields[] = array( 'name' => 'pack_unit', 'value' => 'Pack products QTY');
        $this->fields[] = array( 'name' => 'products_price_pack_unit', 'value' => 'Pack Price', 'set' => 'set_main_products_price',);
        $this->fields[] = array( 'name' => 'products_price_discount_pack_unit', 'value' => 'Pack Quantity Discount Table', 'set' => 'set_products_discount_price');
        foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
            $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_price_pack_unit',
                'name' => 'products_group_price_pack_unit_' . $groups_data['groups_id'],
                'value' => 'Pack Price ' . $groups_data['groups_name'],
                //'get' => 'get_products_price', 'set' => 'set_products_price',
                'set' => 'set_products_price',
                'type' => 'numeric'
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_discount_price_pack_unit',
                'name' => 'products_group_discount_price_pack_unit_' . $groups_data['groups_id'],
                'value' => 'Pack Quantity Discount Table ' . $groups_data['groups_name'],
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table'
            );
        }



        $this->fields[] = array( 'name' => 'outer_carton_dimensions_cm', 'value' => 'Pallet Dimensions (L*W*H) (cm)');
        $this->fields[] = array( 'name' => 'outer_length_cm', 'value' => 'Pallet Length (cm)');
        $this->fields[] = array( 'name' => 'outer_width_cm', 'value' => 'Pallet Width (cm)');
        $this->fields[] = array( 'name' => 'outer_height_cm', 'value' => 'Pallet Height (cm)');
        $this->fields[] = array( 'name' => 'outer_weight_cm', 'value' => 'Pallet Weight (kg)');
        $this->fields[] = array( 'name' => 'packaging', 'value' => 'Pallet Pack QTY');
        $this->fields[] = array( 'name' => 'products_price_packaging', 'value' => 'Pallet Price', 'set' => 'set_main_products_price',);
        $this->fields[] = array( 'name' => 'products_price_discount_packaging', 'value' => 'Pallet Quantity Discount Table', 'set' => 'set_products_discount_price');
        foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
            $data_descriptor = '%|'.TABLE_PRODUCTS_PRICES.'|0|'.$groups_data['groups_id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_price_packaging',
                'name' => 'products_group_price_packaging_' . $groups_data['groups_id'],
                'value' => 'Pallet Price ' . $groups_data['groups_name'],
                //'get' => 'get_products_price', 'set' => 'set_products_price',
                'set' => 'set_products_price',
                'type' => 'numeric'
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_group_discount_price_packaging',
                'name' => 'products_group_discount_price_packaging_' . $groups_data['groups_id'],
                'value' => 'Pallet Quantity Discount Table ' . $groups_data['groups_name'],
                //'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'set' => 'set_products_discount_price',
                'type' => 'price_table'
            );
        }

        if (\common\helpers\Extensions::isAllowed('Inventory')) {
            $this->fields[] = array('name' => 'products_price_full', 'value' => 'Full price?');
            $this->fields[] = array('name' => 'without_inventory', 'value' => 'Disable attributes inventory?');
        }

        $this->fields[] = array( 'name' => 'stock_indication_id', 'value' => 'Stock Availability', 'get' => 'get_stock_indication', 'set' => 'set_stock_indication', );
        $this->fields[] = array( 'name' => 'stock_delivery_terms_id', 'value' => 'Stock Delivery Terms', 'get' => 'get_delivery_terms', 'set' => 'set_delivery_terms', );

        if ( \common\helpers\Extensions::isAllowed('ProductTemplates') ) {
            foreach (\common\classes\platform::getList() as $platformInfo) {
                $this->fields[] = array(
                    'data_descriptor' => '@|assigned_templates|0',
                    'platform_id' => $platformInfo['id'],
                    'name' => 'platform_product_template_' . $platformInfo['id'],
                    'value' => 'Product template - ' . $platformInfo['text'],
                    'get' => 'get_product_template',
                    'set' => 'set_product_template',
                );
            }
        }

        for( $i = 0; $i < $this->max_categories; $i++ ) {
            $this->fields[] = array(
                'data_descriptor' => '@|linked_categories|'.$i,
                'name' => '_categories_'.$i,
                'value' => TEXT_CATEGORIES . '_' . $i,
                'get' => 'get_category',
                'set' => 'set_category',
            );
        }

        foreach(\common\classes\platform::getList() as $platformInfo){
            $this->fields[] = array(
                'data_descriptor' => '@|assigned_platforms|0',
                'name' => 'platform_assign_'.$platformInfo['id'],
                'value' => 'Platform - ' . $platformInfo['text'],
                'get' => 'get_platform',
                'set' => 'set_platform',
            );
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
            if ( $ext::select() ){
                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $this->fields[] = array(
                        'data_descriptor' => '@|assigned_customer_groups|0',
                        'name' => 'customer_group_assigned_' . $groups_data['groups_id'],
                        'value' => 'Assign to customer group - ' . $groups_data['groups_name'],
                        'get' => 'get_products_group_assign', 'set' => 'set_products_group_assign',
                    );
                }
            }
        }


    }

    protected function initAdditionalFields()
    {
        $additional_fields              = array();
        $additional_fields[0]           = array( 'table' => TABLE_MANUFACTURERS, 'link_field' => 'manufacturers_id', 'language_table' => TABLE_MANUFACTURERS_INFO, 'table_prefix' => 'm', 'language_table_prefix' => 'mi', 'language_field' => 'languages_id', 'default_empty' => NULL );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_name', 'value' => 'Manufacturers Name', 'language' => '0' );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_image', 'value' => 'Manufacturers Image', 'language' => '0' );
        $additional_fields[0]['data'][] = array( 'name' => 'manufacturers_url', 'value' => 'Manufacturers URL', 'language' => '1' );

        $this->additional_fields = $additional_fields;
    }

    public function prepareExport($useColumns, $filter){
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $filter_sql .= "AND products_id IN ('".implode("','", array_map('intval',$filter['products_id']))."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
            if ( isset($filter['price_tax']) && $filter['price_tax']>0 ) {
                $this->exportPriceGross = true;
            }
        }
        $main_sql =
            "SELECT {$main_source['select']} products_id, ".
            " manufacturers_id as _manufacturers_id, products_tax_class_id AS _tax_id, ".
            " parent_products_id AS _parent_products_id, products_id_price, products_id_price as _products_id_price, ".
            " products_price AS products_price_def, products_price_discount AS products_price_discount_def ".
            "FROM ".TABLE_PRODUCTS." ".
            "WHERE 1 {$filter_sql} ".
            "/*LIMIT 3*/";

        $this->export_query = tep_db_query( $main_sql );
    }

    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_PRODUCTS_DESCRIPTION ) {
                    $data_sql .= "AND products_id='{$this->data['products_id']}' AND language_id='{$source_data['params'][0]}' AND platform_id='{$source_data['params'][1]}'";
                }elseif ( $source_data['table']==TABLE_PRODUCTS_GROUPS ) {
                    $data_sql .= "AND products_id='{$this->data['products_id']}' AND language_id='{$source_data['params'][0]}'";
                }elseif ( $source_data['table']==TABLE_PRODUCTS_PRICES  ) {
                    $price_product_id = empty($this->data['products_id_price'])?$this->data['products_id']:$this->data['products_id_price'];
                    $data_sql .= "AND products_id='{$price_product_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                }elseif ( $source_data['table']==TABLE_GIFT_WRAP_PRODUCTS  ) {
                    $price_product_id = empty($this->data['products_id_price'])?$this->data['products_id']:$this->data['products_id_price'];
                    $data_sql .= "AND products_id='{$price_product_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                }else{
                    $data_sql .= "AND 1=0 ";
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if ( tep_db_num_rows($data_sql_r)>0 ) {
                    $_data = tep_db_fetch_array($data_sql_r);
                    $this->data = array_merge($this->data, $_data);
                }
            }elseif($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
            }
        }

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }
        return $this->data;
    }

    public function importRow($data, Messages $message)
    {
        $allowInsertNew = true;
        if (is_array($this->import_config) && isset($this->import_config['insert_new']) && $this->import_config['insert_new']=='update'){
            $allowInsertNew = false;
        }
        $generateNewSeo = true;
        if (is_array($this->import_config) && isset($this->import_config['new_seo_name']) && $this->import_config['new_seo_name']=='keep'){
            $generateNewSeo = false;
        }
        if ( is_null($this->makeNetPricesOnImport) ){
            $this->makeNetPricesOnImport = false;
            if (is_array($this->import_config) && isset($this->import_config['imported_price_is_gross']) && $this->import_config['imported_price_is_gross']=='yes'){
                $this->makeNetPricesOnImport = true;
            }
        }

        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;
        $file_primary_columns = $this->file_primary_columns;

        $this->data = $data;
        $this->data['products_id'] = $this->data['products_id'] ?? null;
        $this->data['assign_products_groups_id'] = $this->data['assign_products_groups_id'] ?? null;
        $is_updated = false;
        $need_touch_date_modify = true;
        $_is_product_added = false;

        $mkey_data = [];
        if ( !empty($file_primary_columns) ) {
            $mkey_data = array_intersect_key($data, $file_primary_columns);
            if (count($mkey_data) != count($file_primary_columns)) {
                $mkey_data = [];
            }
        }

        $lookup_by = [];
        if (array_key_exists($file_primary_column, $data)) {
            if ($file_primary_column=='products_id' && intval($data[$file_primary_column])==0){
                $lookup_by = $mkey_data;
            }else {
                $lookup_by = [$file_primary_column => $data[$file_primary_column]];
            }
        }else{
            $lookup_by = $mkey_data;
        }
        if ( count($lookup_by)==0 ){
            throw new EP\Exception('Primary key not found in file');
        }

        if (is_null($this->stock_warning)){
            $this->stock_warning = false;
            if (array_key_exists('products_quantity', $this->data)){
                $warehouses = \common\helpers\Warehouses::get_warehouses(false);
                if ( count($warehouses)>1 ) {
                    $message->info("The update of 'Product quantity' is skipped. Multiple warehouses detected. Instead use Warehouse Stock feed.");
                    $this->stock_warning = true;
                }elseif(\common\helpers\Suppliers::getSuppliersCount(false)>1){
                    $message->info("The update of 'Product quantity' is skipped. Multiple suppliers detected. Instead use Warehouse Stock feed.");
                    $this->stock_warning = true;
                }
            }
        }elseif ($this->stock_warning===true){
            unset($this->data['products_quantity']);
        }

        $price_product_id = false;
        //$file_primary_value = $data[$file_primary_column];
        $file_primary_value = implode(' - ',$lookup_by);
        /*
        $get_main_data_r = tep_db_query(
            "SELECT p.*, COUNT(pa.products_attributes_id) as _attr_counter ".
            "FROM " . TABLE_PRODUCTS . " p ".
            "  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa ON pa.products_id=p.products_id ".
            "WHERE p.{$file_primary_column}='" . tep_db_input($file_primary_value) . "' ".
            "GROUP BY p.products_id"
        );
        $found_rows = tep_db_num_rows($get_main_data_r);*/
        $productsModels = \common\models\Products::find()
            ->where($lookup_by)
            ->all();

        $found_rows = count($productsModels);
        if ($found_rows > 1) {
            unset($productsModels);
            // error data not unique
            $message->info('"'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
            unset($productsModels);
            if ( !$allowInsertNew ){
                $message->info('Missing "'.$file_primary_value.'" - create disabled. Skipped');
                return false;
            }
            // create new entry
            if ( is_null($this->enough_columns_for_create) ) {
                $this->enough_columns_for_create = false;
                foreach ($data_sources as $source_key => $source_data) {
                    if ($source_data['table']) {
                        foreach ($source_data['columns'] as $file_column => $db_column) {
                            if ( $db_column=='products_name' && array_key_exists($file_column, $data) ) {
                                $this->enough_columns_for_create = true;
                            }
                        }
                    }
                }
            }
            if ( !$this->enough_columns_for_create ) {
                $message->info('Can\'t create "'.$file_primary_value.'" - missing product name field(s)');
                return false;
            }

            $create_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;

                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
                }

                if ( array_key_exists($file_column, $this->data) ) {
                    $create_data_array[$db_column] = $this->data[$file_column];
                }
            }
            if ( array_key_exists('products_price_full', $create_data_array) && array_key_exists('without_inventory', $create_data_array) ) {
                if ( (int)$create_data_array['products_price_full'] && (int)$create_data_array['without_inventory'] ){
                    $create_data_array['products_price_full'] = 0;
                    $message->info('Product "'.$file_primary_value.'" can\'t set "Price full" without inventory enabled');
                }
            }

            if ( array_key_exists('weight_cm', $create_data_array) ) {
                $create_data_array['products_weight'] = $create_data_array['weight_cm'];
            }
            foreach ( array('length_', 'width_', 'height_', 'inner_length_', 'inner_width_', 'inner_height_', 'outer_length_', 'outer_width_', 'outer_height_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$create_data_array) ) {
                    $create_data_array[$metricKey.'in'] = 0.393707143*(float)$create_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$create_data_array)){
                    $create_data_array[$metricKey.'cm'] = 2.539959*(float)$create_data_array[$metricKey.'in'];
                }
            }
            foreach ( array('weight_', 'inner_weight_', 'outer_weight_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$create_data_array) ) {
                    $create_data_array[$metricKey.'in'] = 2.20462262*(float)$create_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$create_data_array)){
                    $create_data_array[$metricKey.'cm'] = 0.45359237*(float)$create_data_array[$metricKey.'in'];
                }
            }
            if ( !isset($create_data_array['products_date_added']) ) {
                //$create_data_array['products_date_added'] = 'now()';
                $create_data_array['products_date_added'] = new Expression('NOW()');
            }

            $productModel = new \common\models\Products();
            $productModel->loadDefaultValues();
            $productModel->setAttributes($create_data_array, false);
            $productModel->save(false);
            $products_id = $productModel->products_id;
            //tep_db_perform(TABLE_PRODUCTS, $create_data_array);
            //$products_id = tep_db_insert_id();
            $price_product_id = $products_id;
            if (!empty($create_data_array['parent_products_id']) && !\common\helpers\Product::isSubProductWithPrice()) {
                $price_product_id = false;
            }

            $need_touch_date_modify = false;
            $_is_product_added = true;
            $message->info('Create "'.$file_primary_value.'"');
        } else {
            // update
            /**
             * @var $productModel \common\models\Products
             **/
            $productModel = $productsModels[0];
            $db_main_data = $productModel->getAttributes();
            //$db_main_data = tep_db_fetch_array($get_main_data_r);
            $products_id = $db_main_data['products_id'];
            $price_product_id = $db_main_data['products_id'];
            if ( !empty($db_main_data['parent_products_id']) && !\common\helpers\Product::isSubProductWithPrice() ) {
                $price_product_id = false;
            }

            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;

                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
                }
                if ( array_key_exists($file_column, $this->data) ) {
                    $update_data_array[$db_column] = $this->data[$file_column];
                }
            }

            if ( array_key_exists('products_price_full', $update_data_array) && array_key_exists('without_inventory', $update_data_array) ) {
                if ( (int)$update_data_array['products_price_full'] && (int)$update_data_array['without_inventory'] ){
                    $update_data_array['products_price_full'] = 0;
                    $message->info('Product "'.$file_primary_value.'" can\'t set "Price full" without inventory enabled');
                }
            }

            if ( isset($update_data_array['products_quantity']) ) {
                if ( /*$db_main_data['_attr_counter']>0*/ \common\helpers\Attributes::has_product_attributes($products_id,true) ) {
                    unset($update_data_array['products_quantity']);
                }else{
                    if ( false && $db_main_data['products_status'] && $db_main_data['products_quantity']>0 && $update_data_array['products_quantity']<=0 ) {
                        $switch_off_stock_ids = \common\classes\StockIndication::productDisableByStockIds();
                        if ( isset($update_data_array['stock_indication_id']) && isset( $switch_off_stock_ids[$update_data_array['stock_indication_id']] ) ) {
                            $update_data_array['products_status'] = 0;
                            $message->info('Product "'.$file_primary_value.'" disabled');
                        }elseif ( isset($db_main_data['stock_indication_id']) && isset( $switch_off_stock_ids[$db_main_data['stock_indication_id']] ) ) {
                            $update_data_array['products_status'] = 0;
                        }
                    }
                }
            }
            if ( array_key_exists('weight_cm', $update_data_array) ) {
                $update_data_array['products_weight'] = $update_data_array['weight_cm'];
            }
            foreach ( array('length_', 'width_', 'height_', 'inner_length_', 'inner_width_', 'inner_height_', 'outer_length_', 'outer_width_', 'outer_height_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$update_data_array) ) {
                    $update_data_array[$metricKey.'in'] = 0.393707143*(float)$update_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$update_data_array)){
                    $update_data_array[$metricKey.'cm'] = 2.539959*(float)$update_data_array[$metricKey.'in'];
                }
            }
            foreach ( array('weight_', 'inner_weight_', 'outer_weight_', ) as $metricKey ) {
                if ( array_key_exists($metricKey.'cm',$update_data_array) ) {
                    $update_data_array[$metricKey.'in'] = 2.20462262*(float)$update_data_array[$metricKey.'cm'];
                }elseif ( array_key_exists($metricKey.'in',$update_data_array)){
                    $update_data_array[$metricKey.'cm'] = 0.45359237*(float)$update_data_array[$metricKey.'in'];
                }
            }

            if ( empty($update_data_array['products_date_added']) ) {
                unset($update_data_array['products_date_added']);
            }

            // unset($update_data_array['parent_products_id']); //?????

            if (count($update_data_array) > 0) {
                $update_data_array['products_last_modified'] = new Expression('NOW()');
                $productModel->setAttributes($update_data_array,false);
                $productModel->save(false);
                //$update_data_array['products_last_modified'] = 'now()';
                //tep_db_perform(TABLE_PRODUCTS, $update_data_array, 'update', "products_id='" . (int)$products_id . "'");
                $is_updated = true;
                $need_touch_date_modify = false;
            }
        }

        $this->data['products_id'] = $products_id;

        $productModel->refresh();
        if ( array_key_exists('_products_id_price', $this->data) && !empty($productModel->parent_products_id) ) {
            if ( $this->data['_products_id_price'] ) {
                $productModel->setAttributes(['products_id_price' => $productModel->products_id], false);
            }else{
                $productModel->setAttributes(['products_id_price' => $productModel->parent_products_id], false);
            }
        }
        $productModel->save(false);

        $seo_page_notice = [];

        $languages_filled = false;
        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $data)) continue;
                    if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
                    }
                    $new_data[$db_column] = $this->data[$file_column] ?? null;
                }
                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select_raw']} 1 AS _dummy".($source_data['table']==TABLE_PRODUCTS_DESCRIPTION?', products_seo_page_name as products_seo_page_name_check':'')." FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_PRODUCTS_DESCRIPTION) {
                    $update_pk = "products_id='{$products_id}' AND language_id='{$source_data['params'][0]}' AND platform_id='{$source_data['params'][1]}'";
                    $insert_pk = array('products_id' => $products_id, 'language_id' => $source_data['params'][0], 'platform_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";

                    //if (defined('MSEARCH_ENABLE') && strtolower(MSEARCH_ENABLE)=='soundex') {
                        if (isset($new_data['products_name']) && strlen($new_data['products_name']) > 0) {
                            $products_name_keywords = array_filter(preg_split('/[\s]+/', strip_tags($new_data['products_name'])), function ($__word) {
                                return strlen($__word) >= (defined('BACKEND_MSEARCH_WORD_LENGTH')?intval(BACKEND_MSEARCH_WORD_LENGTH):0);
                            });
                            if (count($products_name_keywords) > 0) {
                                $ks_hash = tep_db_fetch_array(tep_db_query(
                                    "SELECT CONCAT(" . implode(', ', array_map(function ($_word) {
                                        return 'SOUNDEX(\'' . tep_db_input($_word) . '\'),\',\'';
                                    }, $products_name_keywords)) . ") AS sx"
                                ));
                                if (is_array($ks_hash)) {
                                    $new_data['products_name_soundex'] = implode(',', array_unique(preg_split('/,/', $ks_hash['sx'], -1, PREG_SPLIT_NO_EMPTY)));
                                }
                            }
                        }

                        if (isset($new_data['products_description']) && strlen($new_data['products_description']) > 0) {
                            $products_description_keywords = array_filter(preg_split('/[\s]+/', strip_tags($new_data['products_description'])), function ($__word) {
                                return strlen($__word) >= (defined('BACKEND_MSEARCH_WORD_LENGTH')?intval(BACKEND_MSEARCH_WORD_LENGTH):0);
                            });
                            if (count($products_description_keywords) > 0) {
                                $ks_hash = tep_db_fetch_array(tep_db_query(
                                    "SELECT CONCAT(" . implode(', ', array_map(function ($_word) {
                                        return 'SOUNDEX(\'' . tep_db_input($_word) . '\'),\',\'';
                                    }, $products_description_keywords)) . ") AS sx"
                                ));
                                if (is_array($ks_hash)) {
                                    $new_data['products_description_soundex'] = implode(',', array_unique(preg_split('/,/', $ks_hash['sx'], -1, PREG_SPLIT_NO_EMPTY)));
                                }
                            }
                        }
                    //}

                    if ( $generateNewSeo /*|| $_is_product_added*/ ) {
                        $new_data['products_seo_page_name'] = \common\helpers\Seo::makeProductSlug($new_data, $products_id);
                    }elseif($_is_product_added || array_key_exists('products_seo_page_name',$new_data)){
                        $prev_seo_name = $new_data['products_seo_page_name'];
                        $new_data['products_seo_page_name'] = \common\helpers\Seo::makeProductSlug($new_data, $products_id);
                        if (!empty($prev_seo_name) && $prev_seo_name != $new_data['products_seo_page_name'] && !isset($seo_page_notice[$prev_seo_name.'#'.$new_data['products_seo_page_name']])) {
                            $message->info('[!] "' . $file_primary_value . '" seo page name changed from "' . $prev_seo_name . '" to "' . $new_data['products_seo_page_name'] . '"');
                            $seo_page_notice[$prev_seo_name.'#'.$new_data['products_seo_page_name']] = 1;
                        }
                    }


                    /*if (empty($new_data['products_seo_page_name'])) {
                      $new_data['products_seo_page_name'] = $products_id;
                    }*/
/*                } elseif ($source_data['table'] == TABLE_PRODUCTS_GROUPS) {
                    $update_pk = "products_id='{$products_id}' AND language_id='{$source_data['params'][0]}'";
                    $insert_pk = array('products_id' => $products_id, 'language_id' => $source_data['params'][0]);
                    $data_sql .= "AND {$update_pk}";

                    if (empty($new_data['products_seo_page_name'])) {
                        $new_data['products_seo_page_name'] = $products_id;
                    }
*/
                } elseif ($source_data['table'] == TABLE_PRODUCTS_PRICES && $price_product_id) {
                    $update_pk = "products_id='{$price_product_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                    $insert_pk = array('products_id' => $price_product_id, 'currencies_id' => $source_data['params'][0], 'groups_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";
                } else {
                    continue;
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if (tep_db_num_rows($data_sql_r) > 0) {
                    $_old_data = tep_db_fetch_array($data_sql_r);
                    tep_db_free_result($data_sql_r);
                    //echo '<pre>update rel '; var_dump($source_data['table'],$new_data,'update', $update_pk); echo '</pre>';
                    tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);

                    if ( $source_data['table']==TABLE_PRODUCTS_DESCRIPTION ) {
                        if (!array_key_exists('products_seo_page_name',$_old_data)) {
                            $_old_data['products_seo_page_name'] = $_old_data['products_seo_page_name_check'];
                        }
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
                            $ext::trackProductLinks($products_id, $insert_pk['language_id'], $insert_pk['platform_id'], $new_data, $_old_data);
                        }
                    }
                } else {
                    //echo '<pre>insert rel '; var_dump($source_data['table'],array_merge($new_data,$insert_pk)); echo '</pre>';
                    tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                    if ( !$languages_filled && $source_data['table']==TABLE_PRODUCTS_DESCRIPTION && $_is_product_added ) {
                        static $_platform_list;
                        if ( !is_array($_platform_list) ) {
                            $_platform_list = \common\models\Platforms::getPlatformsByType("non-virtual")->asArray()->select(['platform_id'])->all();
                        }
                        foreach($_platform_list as $_fill_platform_id) {
                            foreach (\common\helpers\Language::get_languages() as $_lang) {
                                if ($insert_pk['language_id'] == $_lang['id'] && $_fill_platform_id['platform_id']==$insert_pk['platform_id']) continue;
                                $description_pk = $insert_pk;
                                $description_pk['language_id'] = $_lang['id'];
                                $description_pk['platform_id'] = $_fill_platform_id['platform_id'];
                                tep_db_perform($source_data['table'], array_merge($new_data, $description_pk));
                            }
                        }
                        $languages_filled = true;
                    }
                }
                $is_updated = true;
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
                    }
                }
            }
        }

        if ($is_updated && $need_touch_date_modify) {
            //-- products_seo_page_name
            tep_db_perform(TABLE_PRODUCTS, array(
                'products_last_modified' => 'now()',
            ), 'update', "products_id='" . (int)$products_id . "'");

            //products_name_soundex, products_description_soundex, products_seo_page_name
        }
        if ( $_is_product_added ) {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$products_id."' "));
            if ( $_check['c']==0 ) {
                tep_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, array(
                    'products_id' => $products_id,
                    'categories_id' => 0,
                ));
            }
            \common\helpers\Product::fillGlobalSort(0, $products_id);
            // {{ assign to default sales channel
            static $available_channels;
            if ( !is_array($available_channels) ) {
                $available_channels = array_map(function($i){
                    return $i['id'];
                    },
                    \common\classes\platform::getList(false, false)
                );
            }
            if ( is_array($available_channels) && count($available_channels)>0 ){
                $anyAssigned = \Yii::$app->getDb()->createCommand(
                    "SELECT COUNT(*) ".
                    "FROM ".\common\models\PlatformsProducts::tableName()." ".
                    "WHERE products_id='".(int)$products_id."' AND platform_id IN ('".implode("','", $available_channels)."')"
                )->queryScalar();
                if ( !$anyAssigned ){
                    $assignToDefault = new \common\models\PlatformsProducts([
                        'products_id' => (int)$products_id,
                        'platform_id' => (int)$available_channels[0],
                    ]);
                    $assignToDefault->save(false);
                }
            }
            // }} assign to default sales channel
        }
        if ( array_key_exists('products_quantity', $this->data) && $this->data['products_quantity']>0 /* ?? */ ) {
            global $login_id;
            $quantity = (int)$this->data['products_quantity'];
            $productId = trim((int)$products_id);
            \common\models\WarehousesProducts::deleteAll([
                'prid' => $productId,
                'products_id' => $productId
            ]);
            $locationId = 0;
            foreach (\common\helpers\Product::getWarehouseIdPriorityArray($productId, $quantity, false) as $warehouseId) {
                foreach (\common\helpers\Product::getSupplierIdPriorityArray($productId) as $supplierId) {
                    $quantity = \common\helpers\Warehouses::update_products_quantity($productId, $warehouseId, $quantity, ($quantity > 0 ? '+' : '-'), $supplierId, $locationId, [
                        'comments' => 'EP Stock feed update',
                        'admin_id' => $login_id
                    ]);
                    if ($quantity > 0) {
                        \common\helpers\Product::doCache($productId);
                    }
                    break 2;
                }
            }
        }

        /* @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription */
        $ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed');
        if ($ext && $ext::isEnabled()) {
          $ext::reindex((int)$products_id);
        }

        \common\helpers\ProductsGroupSortCache::update((int)$products_id);

        $this->entry_counter++;

        return true;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' products');
        $message->info('Done.');

        $this->EPtools->done('products_import');
    }

    public function set_date($field_data, $products_id, $message)
    {
        $file_value = $this->data[$field_data['name']];
        if ( empty($this->data[$field_data['name']]) ) {
            unset($this->data[$field_data['name']]);
        }else{
            $this->data[$field_data['name']] = EP\Tools::getInstance()->parseDate($file_value);
            if ((int)$this->data[$field_data['name']] < 1980) {
                unset($this->data[$field_data['name']]);
                $message->info($field_data['value'] . ' "' . $file_value . '" not valid');
            }
        }
        return $this->data[$field_data['name']] ?? null;
    }

    function linked_categories($link)
    {
        if ( !isset($this->data['assigned_categories']) ) {
            $this->data['assigned_categories'] = array();
            $this->data['assigned_categories_ids'] = array();
            $get_categories_r = tep_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$this->data['products_id']."' ORDER BY categories_id");
            while( $get_category = tep_db_fetch_array($get_categories_r) ) {
                $this->data['assigned_categories_ids'][] = (int)$get_category['categories_id'];
                $this->data['assigned_categories'][] = $this->EPtools->tep_get_categories_full_path((int)$get_category['categories_id']);
            }
            $this->data['assign_categories_ids'] = array();
        }
    }

    function get_category($field_data)
    {
        $idx = intval(str_replace('_categories_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_categories'][$idx])?$this->data['assigned_categories'][$idx]:'';
        return $this->data[$field_data['name']];
    }

    function set_category($field_data, $products_id)
    {
        $import_category_path = $this->data[$field_data['name']];
        $category_id = $this->EPtools->tep_get_categories_by_name($import_category_path);
        if ( is_numeric($category_id) ) {
            $this->data['assign_categories_ids'] = $category_id;
            tep_db_query("INSERT IGNORE INTO ".TABLE_PRODUCTS_TO_CATEGORIES." (products_id, categories_id) VALUES('".(int)$products_id."', '".(int)$category_id."')");
        }
        return ;
    }

    function assigned_products_groups($link)
    {
        if ( !isset($this->data['assigned_products_groups']) ) {
            $this->data['assigned_products_groups'] = array();
            $r = tep_db_query("SELECT pg.products_groups_name, pg.language_id FROM " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_GROUPS . " pg on pg.products_groups_id=p.products_groups_id WHERE p.products_id='".$this->data['products_id']."' ");
            while( $d = tep_db_fetch_array($r) ) {
                $this->data['assigned_products_groups'][$d['language_id']] = $d['products_groups_name'];
            }
        }
    }

    function set_products_groups($field_data, $products_id)
    {
      $lang_id = intval(str_replace('products_groups_name_','', $field_data['name']));
      $products_groups_name = $this->data[$field_data['name']];
        $products_groups_id = $this->EPtools->tep_get_products_groups_by_name($products_groups_name, $this->data['assign_products_groups_id'], $lang_id);
        if ( !isset($this->data['assign_products_groups_id']) && is_numeric($products_groups_id) ) {
            $this->data['assign_products_groups_id'] = $products_groups_id;
            tep_db_query("update " . TABLE_PRODUCTS . " set products_groups_id='". (int)$products_groups_id . "' where products_id='" . (int)$products_id . "'");
        }
        return ;
    }

    function get_products_groups( $field_data, $products_id )
    {
      $lang_id = intval(str_replace('products_groups_name_','', $field_data['name']));
      $this->data[$field_data['name']] = isset($this->data['assigned_products_groups'][$lang_id])?$this->data['assigned_products_groups'][$lang_id]:'';
      return $this->data[$field_data['name']];
    }

    function assigned_platforms()
    {
        if ( !isset($this->data['assigned_platforms']) ) {
            $this->data['assigned_platforms'] = [];
            $get_assigned_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS_PRODUCTS." WHERE products_id='".$this->data['products_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_platforms'][ (int)$_assigned['platform_id'] ] = (int)$_assigned['platform_id'];
                }
            }
        }
    }

    function get_platform($field_data)
    {
        $idx = intval(str_replace('platform_assign_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_platforms'][$idx])?'1':'';
        return $this->data[$field_data['name']];
    }

    function set_platform($field_data, $products_id)
    {
        if ( empty($products_id) || !array_key_exists($field_data['name'], $this->data)) return;

        $platform_id = intval(str_replace('platform_assign_','', $field_data['name']));
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }

        if ( isset($this->data['assigned_platforms'][$platform_id]) ) {
            if (!$fileValue) {
                tep_db_query("DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." WHERE platform_id='".(int)$platform_id."' AND products_id='".(int)$products_id."'");
            }
        }else{
            if ($fileValue) {
                tep_db_query("INSERT IGNORE INTO ".TABLE_PLATFORMS_PRODUCTS." (platform_id, products_id) VALUES('".(int)$platform_id."', '".(int)$products_id."')");
            }
        }
    }

    function assigned_customer_groups()
    {
        if ( !isset($this->data['assigned_customer_groups']) && \Yii::$app->db->schema->getTableSchema('groups_products')) {
            $this->data['assigned_customer_groups'] = [];
            $get_assigned_r = tep_db_query("SELECT groups_id FROM groups_products WHERE products_id='".$this->data['products_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_customer_groups'][ (int)$_assigned['groups_id'] ] = (int)$_assigned['groups_id'];
                }
            }
        }
    }

    function get_products_group_assign($field_data)
    {
        $idx = intval(str_replace('customer_group_assigned_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_customer_groups'][$idx])?'1':'';
        return $this->data[$field_data['name']];
    }

    function set_products_group_assign($field_data, $products_id)
    {
        if ( empty($products_id) || !array_key_exists($field_data['name'], $this->data)) return;

        $groups_id = intval(str_replace('customer_group_assigned_','', $field_data['name']));
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }

        if (!\Yii::$app->db->schema->getTableSchema('groups_products')) return;
        if ( isset($this->data['assigned_customer_groups'][$groups_id]) ) {
            if (!$fileValue) {
                tep_db_query("DELETE FROM groups_products WHERE groups_id='".(int)$groups_id."' AND products_id='".(int)$products_id."'");
            }
        }else{
            if ($fileValue) {
                tep_db_query("INSERT IGNORE INTO groups_products (groups_id, products_id) VALUES('".(int)$groups_id."', '".(int)$products_id."')");
            }
        }
    }

    function get_products_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            if ( isset($field_data['isMain']) && $field_data['isMain'] ){
                $this->data[$field_data['name']] = $this->data['products_price_def'];
            } else {
                $this->data[$field_data['name']] = 'same'/*'-2'*/;
            }
        }elseif( floatval($this->data[$field_data['name']])==-2 ) {
            $this->data[$field_data['name']] = 'same';
        }elseif( floatval($this->data[$field_data['name']])==-1 ) {
            $this->data[$field_data['name']] = 'disabled';
        }
        if ( $this->exportPriceGross && is_numeric($this->data[$field_data['name']]) && $this->data[$field_data['name']]>0 ){
            if ( !array_key_exists('products_tax_class_id',$this->data) ){
                $this->data['products_tax_class_id'] = $this->data['_tax_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']]*((100+$this->getTaxRate())/100),6,'.','');
        }
        return $this->data[$field_data['name']];
    }

    function set_products_price( $field_data, $products_id )
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-2 || $this->data[$field_data['name']]=='same' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disabled' ) {
            $this->data[$field_data['name']] = '-1';
        }
        if ( floatval($this->data[$field_data['name']])>0 ) {
            if($this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        }
        if ( isset($field_data['isMain']) && $field_data['isMain'] ){
            $product = \common\models\Products::findOne(['products_id'=>$products_id]);
            if ( $product ) {
                $product->setAttributes(['products_price'=>$this->data[$field_data['name']]], false);
                $product->save(false);
            }
        }
        return '';
    }

    function set_bonus_point_price( $field_data, $products_id )
    {
        if ( isset($field_data['isMain']) && $field_data['isMain'] ){
            $product = \common\models\Products::findOne(['products_id'=>$products_id]);
            if ( $product ) {
                $product->setAttributes([$field_data['column_db']=>(float)$this->data[$field_data['name']]], false);
                $product->save(false);
            }
        }
        return '';
    }

    function set_main_products_price( $field_data, $products_id )
    {
        if( floatval($this->data[$field_data['name']])>0 ) {
            //$this->data[$field_data['name']] = '-1';
            if($this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        }
        return $this->data[$field_data['name']];
    }

    function get_products_discount_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            if ( isset($field_data['isMain']) && $field_data['isMain'] ){
                $this->data[$field_data['name']] = $this->data['products_price_discount_def'];
            }
        }
        if ( $this->exportPriceGross && !empty($this->data[$field_data['name']]) ) {
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_tax_id'];
            }
            $taxRate = $this->getTaxRate();
            if ( $taxRate ) {
                $ar = preg_split('/[; :]/', $this->data[$field_data['name']], -1, PREG_SPLIT_NO_EMPTY);
                $clean = '';
                if (is_array($ar) && count($ar) > 0 && count($ar) % 2 == 0) {
                    $tmp = [];
                    for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                        $tmp[intval($ar[$i])] = floatval($ar[$i + 1]);
                    }
                    ksort($tmp, SORT_NUMERIC);
                    foreach ($tmp as $key => $value) {
                        if ($key < 2) { // q-ty discount should start from 2+
                            $clean = '';
                            break;
                        }
                        if (is_numeric($value) && $value > 0) {
                            $value = $value * ((100 + $taxRate) / 100);
                        }
                        $clean .= $key . ':' . $value . ';';
                    }
                }
                $this->data[$field_data['name']] = $clean;
            }
        }
        return $this->data[$field_data['name']] ?? null;
    }

    function set_products_discount_price( $field_data, $products_id )
    {
        if( !empty($this->data[$field_data['name']])) {
          $ar = preg_split('/[; :]/', $this->data[$field_data['name']], -1, PREG_SPLIT_NO_EMPTY);
          $clean = '';
          if (is_array($ar) && count($ar)>0 && count($ar)%2==0) {
             $tmp = [];
             for ($i=0, $n=sizeof($ar); $i<$n; $i=$i+2) {
                $tmp[intval($ar[$i])] = floatval($ar[$i+1]);
             }
             ksort($tmp, SORT_NUMERIC);
             foreach ($tmp as $key => $value) {
               if ($key<2) { // q-ty discount should start from 2+
                 $clean = '';
                 break;
               }
               if (is_numeric($value) && $value>0) {
                   if( $this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                       $value = $value*(100/(100+$taxRate));
                   }
               }
               $clean .= $key . ':' .$value . ';';
             }
          }
          $this->data[$field_data['name']] = $clean;

          if ( isset($field_data['isMain']) && $field_data['isMain'] ){
            $product = \common\models\Products::findOne(['products_id'=>$products_id]);
            if ( $product ) {
                $product->setAttributes(['products_price_discount'=>$this->data[$field_data['name']]], false);
                $product->save(false);
            }
          }
        }
        return '';
    }

    function get_brand( $field_data, $products_id ){
        static $brands = false;
        if ( !is_array($brands) ) {
            $brands = array();
            $get_brands_r = tep_db_query("SELECT manufacturers_id, manufacturers_name FROM ".TABLE_MANUFACTURERS);
            if ( tep_db_num_rows($get_brands_r)>0 ) {
                while( $get_brand = tep_db_fetch_array($get_brands_r) ) {
                    $brands[$get_brand['manufacturers_id']] = $get_brand['manufacturers_name'];
                }
            }
        }
        $this->data['manufacturers_id'] = isset($brands[$this->data['manufacturers_id']])?$brands[$this->data['manufacturers_id']]:'';
        return $this->data['manufacturers_id'];
    }

    function set_brand( $field_data, $products_id ){
        $this->data['manufacturers_id'] = $this->EPtools->get_brand_by_name($this->data['manufacturers_id']);
    }

    function set_tax_class($field_data, $products_id, $message){
        static $fetched_map = array();
        $file_value = trim($this->data['products_tax_class_id']);
        $tax_class_id = 0;

        if ( !isset($fetched_map[$file_value]) ) {
            if ( empty($file_value) ) {
                $tax_class_id = 0;
            }else
                if ( is_numeric($file_value) ) {
                    $check_number = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS cnt FROM " . TABLE_TAX_CLASS . " WHERE tax_class_id='" . (int)$file_value . "' "));
                    if (is_array($check_number) && $check_number['cnt'] > 0) {
                        $fetched_map[$file_value] = (int)$file_value;
                        $tax_class_id = (int)$file_value;
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 0;
                    }
                }else{
                    $get_by_name_r = tep_db_query(
                        "SELECT tax_class_id FROM " . TABLE_TAX_CLASS . " WHERE tax_class_title='" . tep_db_input($file_value) . "' LIMIT 1"
                    );
                    if (tep_db_num_rows($get_by_name_r)>0) {
                        $get_by_name = tep_db_fetch_array($get_by_name_r);
                        $fetched_map[$file_value] = $get_by_name['tax_class_id'];
                        $tax_class_id = $get_by_name['tax_class_id'];
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 0;
                    }
                }
        }else{
            $tax_class_id = $fetched_map[$file_value];
        }
        $this->data['products_tax_class_id'] = $tax_class_id;
        return $tax_class_id;
    }

    protected function getTaxRate()
    {
        $taxRate = 0;
        if ( isset($this->data['_tax_rate']) ) {
            $taxRate = $this->data['_tax_rate'];
        }else {
            $tax_class_id = 0;
            if (array_key_exists('products_tax_class_id', $this->data)) {
                if ( !is_numeric($this->data['products_tax_class_id']) ) {
                    $this->set_tax_class([],null,null);
                }
                $tax_class_id = $this->data['products_tax_class_id'];
            }elseif( $this->data['products_id'] ){
                $tax_class_id = \common\helpers\Product::get_products_info($this->data['products_id'],'products_tax_class_id');
            }
            if ( $tax_class_id ) {
                $defaultAddress = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getPlatformAddress();
                $country_id = $defaultAddress['country_id'];
                $zone_id = $defaultAddress['zone_id'];

                $tax_query = tep_db_query(
                    "select sum(tax_rate) as tax_rate ".
                    "from " . TABLE_TAX_RATES . " tr ".
                    "  left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) ".
                    "  left join " . TABLE_TAX_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) ".
                    "where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') ".
                    " and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "') ".
                    " and tr.tax_class_id = '" . (int) $tax_class_id . "' ".
                    "group by tr.tax_priority"
                );

                if (tep_db_num_rows($tax_query)) {
                    $tax_multiplier = 1.0;
                    while ($tax = tep_db_fetch_array($tax_query)) {
                        $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
                    }
                    $taxRate = ($tax_multiplier - 1.0) * 100;
                }
            }
            $this->data['_tax_rate'] = $taxRate;
        }
        return $this->data['_tax_rate'];
    }

    function get_tax_class($field_data, $products_id){
        static $fetched = false;
        if ( !is_array($fetched) ) {
            $fetched = array();
            $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS );
            if ( tep_db_num_rows($tax_class_query)>0 ) {
                while ($tax_class = tep_db_fetch_array($tax_class_query)) {
                    $fetched[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
                }
            }
        }
        $this->data['products_tax_class_id'] = isset( $fetched[$this->data['products_tax_class_id']] )?$fetched[$this->data['products_tax_class_id']]:'';
        return $this->data['products_tax_class_id'];
    }

    function get_delivery_terms($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockDeliveryTerms($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_delivery_terms($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockDeliveryTermId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function get_stock_indication($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockIndication($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_stock_indication($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockIndicationId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function get_pctemplate($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getPcTemplateName($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_pctemplate($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupPcTemplateId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function assigned_templates()
    {
        if ( !isset($this->data['assigned_templates']) ) {
            $this->data['assigned_templates'] = [];
            $get_assigned_r = tep_db_query("SELECT platform_id, theme_name, template_name FROM ".TABLE_PRODUCT_TO_TEMPLATE." WHERE products_id='".$this->data['products_id']."'");
            if ( tep_db_num_rows($get_assigned_r)>0 ){
                while($_assigned = tep_db_fetch_array($get_assigned_r)){
                    $this->data['assigned_templates'][ (int)$_assigned['platform_id'] ] = $_assigned;
                }
            }
        }
    }

    function get_product_template($field_data)
    {
        $idx = intval($field_data['platform_id']);
        $this->data[$field_data['name']] = '';
        if ( isset($this->data['assigned_templates'][$idx]['template_name']) ){
            $this->data[$field_data['name']] = $this->data['assigned_templates'][$idx]['template_name'];
        }
        return $this->data[$field_data['name']];
    }

    function set_product_template($field_data, $products_id, $messages)
    {
        if ( empty($products_id) || !array_key_exists($field_data['name'], $this->data)) return;

        $platform_id = intval($field_data['platform_id']);
        $fileValue = trim($this->data[$field_data['name']]);
        if ( isset($this->data['assigned_templates'][$platform_id]) && empty($fileValue) ){
            \common\models\ProductToTemplate::deleteAll(['platform_id' => (int)$platform_id, 'products_id' => $products_id]);
        }
        if ( !empty($fileValue) ){
            // validate value
            static $allowed_product_templates;
            if ( !is_array($allowed_product_templates) ) {
                $allowed_product_templates = [];
                $platform_themes = \Yii::$app->getDb()->createCommand(
                    "SELECT p2t.platform_id, t.theme_name, t.title ".
                    "FROM " . TABLE_THEMES . " t ".
                    "left join " . TABLE_PLATFORMS_TO_THEMES . " p2t on t.id = p2t.theme_id ".
                    "WHERE p2t.is_default = 1"
                )->queryAll();
                foreach ($platform_themes as $platform_theme){
                    $products_templates = [];
                    foreach(\Yii::$app->getDb()->createCommand(
                        "select setting_value ".
                        "from " . TABLE_THEMES_SETTINGS . " ".
                        "where theme_name = :theme_name and setting_group = 'added_page' and setting_name = 'product'",
                        [':theme_name'=>(string)$platform_theme['theme_name']]
                    )->queryColumn() as $product_page_name){
                        $products_templates[strtolower($product_page_name)] = $product_page_name;
                    }
                    $allowed_product_templates[ $platform_theme['platform_id'] ] = [
                        'theme_name' => $platform_theme['theme_name'],
                        'templates' => $products_templates,
                    ];
                }
            }
            $assign_data = [];
            if ( isset($allowed_product_templates[$platform_id]) ) {
                if ( isset($allowed_product_templates[$platform_id]['templates'][strtolower($fileValue)]) ){
                    $assign_data = [
                        'products_id' => $products_id,
                        'platform_id' => $platform_id,
                        'theme_name' => $allowed_product_templates[$platform_id]['theme_name'],
                        'template_name' => $allowed_product_templates[$platform_id]['templates'][strtolower($fileValue)],
                    ];
                }else{
                    if ( is_object($messages) && $messages instanceof Messages ){
                        $messages->info('"'.$field_data['value'].'" value "'.$fileValue.'" is not in valid range ['.implode('; ', $allowed_product_templates[$platform_id]['templates']).']');
                    }
                }
            }
            if ( empty($assign_data) ) {
                \common\models\ProductToTemplate::deleteAll(['platform_id' => (int)$platform_id, 'products_id' => $products_id]);
            }else{
                $updateModel = \common\models\ProductToTemplate::findOne(['platform_id' => (int)$platform_id, 'products_id' => $products_id]);
                if ( !$updateModel ){
                    $updateModel = new \common\models\ProductToTemplate(['platform_id' => (int)$platform_id, 'products_id' => $products_id]);
                    $updateModel->loadDefaultValues();
                }
                $updateModel->setAttributes($assign_data, false);
                $updateModel->save(false);
            }
        }
    }

    function set_parent_product_model($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $target_key = $field_data['name'];
        $this->data[$target_key] = 0;
        if ( !empty($textValue) ) {
            $matchedParents = \common\models\Products::find()
                ->where(['products_model' => $textValue])
                ->select(['products_id'])
                ->asArray()
                ->all();
            if ( count($matchedParents)>1 ) {
                if ( is_object($matchedParents) && $matchedParents instanceof Messages) {
                    $message->info("[!] Found ".count($matchedParents)." match parent model \"".$textValue."\" - skipped");
                }
            }elseif ( count($matchedParents)==0 ) {
                if ( is_object($matchedParents) && $matchedParents instanceof Messages) {
                    $message->info("[!] Not Found parent model \"".$textValue."\" - skipped");
                }
            }else{
                $this->data[$target_key] = (int)$matchedParents[0]['products_id'];
            }
        }

        return $this->data[$target_key];
    }

    function get_parent_product_model($field_data, $products_id)
    {
        $this->data[$field_data['name']] = '';
        if ($this->data['_parent_products_id']){
            $this->data[$field_data['name']] = \common\helpers\Product::get_products_info($this->data['_parent_products_id'],'products_model');
        }
        return $this->data[$field_data['name']];
    }

    function get_gift_wrap_price($field_data, $products_id)
    {
        if ( !array_key_exists($field_data['name'],$this->data) ){
            $this->data[$field_data['name']] = '';
        }elseif ( $this->exportPriceGross && floatval($this->data[$field_data['name']])>0 ) {
            if ( !array_key_exists('products_tax_class_id',$this->data) ){
                $this->data['products_tax_class_id'] = $this->data['_tax_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']]*((100+$this->getTaxRate())/100),6,'.','');
        }
        return $this->data[$field_data['name']];
    }

    function set_gift_wrap_price($field_data, $products_id, $message = false)
    {
        if ( !array_key_exists($field_data['name'], $this->data) ) {
            return;
        }
        $file_value = trim($this->data[$field_data['name']]);
        $pk_data = [
            'products_id' => $products_id,
            'groups_id' => $field_data['key_columns']['groups_id'],
            'currencies_id' => $field_data['key_columns']['currencies_id'],
        ];
        if ( strlen($file_value)==0 ){
            \common\models\GiftWrapProducts::deleteAll($pk_data);
        }else{
            if($this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                $file_value = $file_value*(100/(100+$taxRate));
            }

            $priceModel = \common\models\GiftWrapProducts::find()->where($pk_data)->one();
            if ( !$priceModel ){
                $priceModel = new \common\models\GiftWrapProducts($pk_data);
                $priceModel->loadDefaultValues();
            }
            $priceModel->setAttributes(['gift_wrap_price'=>$file_value], false);
            $priceModel->save(false);
        }
    }

    function get_child_with_own_price($field_data, $products_id)
    {
        if ($this->data['_parent_products_id'] && $this->data['products_id_price']==$this->data['products_id']){
            $this->data[$field_data['name']] = 'Yes';
        }else{
            $this->data[$field_data['name']] = '';
        }
        return $this->data[$field_data['name']];
    }

    function set_child_with_own_price($field_data, $products_id, $message = false)
    {
        $fileValue = 0;
        if (is_numeric($this->data[$field_data['name']])){
            $fileValue = intval($this->data[$field_data['name']])?1:0;
        }elseif ( !empty($this->data[$field_data['name']]) && in_array(strtolower($this->data[$field_data['name']]),['y','yes','true','1']) ) {
            $fileValue = 1;
        }
        $this->data[$field_data['name']] = $fileValue;
        return $this->data[$field_data['name']];
    }

}