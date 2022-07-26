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
use common\classes\Order;

class OrderExport extends ProviderAbstract implements ExportInterface, ImportInterface
{
    protected $fields = array();
    protected $export_query;

    /**
     * @var EP\Tools
     */
    protected $EPtools;

    function __construct( )
    {
        parent::__construct();
        $this->initFields();

        $this->EPtools = new EP\Tools();

    }

    protected function initFields()
    {
        /*
        статистика продаж:
        запрос: дата (варианты:
        от дня - до дня.
        день/месяц/год
        месяц/год
        год
        all
        )
        от дня до дня - это кастомно, т.е. надо такой период выбирать
        день/месяц/год - конкретный один день
        месяц/год - конкретный один месяц
        год - конкретный один год
        all - весь период

        Ответ (за указанный период):
        количество заказов
        количество клиентов
        общая сумма заказов - брать только из группы Completed Orders)
         */
        $this->fields[] = array( 'name' => 'result', 'value' => 'row', 'is_key'=>true );
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            $order_filter = (isset($filter['order']) && is_array($filter['order']))?$filter['order']:[];

            if (isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='exact')
            {
                if (!empty($order_filter['date_from'])) {
                    $filter_sql .= " AND o.date_purchased >= '" . tep_db_input(substr($order_filter['date_from'], 0, 10)) . " 00:00:00' ";
                }
                if (!empty($order_filter['date_to'])) {
                    $filter_sql .= " AND o.date_purchased <= '" . tep_db_input(substr($order_filter['date_to'], 0, 10)) . " 23:59:59' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='year/month')
            {
                $year = $order_filter['year'];
                $filter_sql .= " AND YEAR(o.date_purchased)='" . tep_db_input($year) . "' ";
                $month = $order_filter['month'];
                if ( !empty($month) ) {
                    $filter_sql .= " AND DATE_FORMAT(o.date_purchased,'%Y%m')='".tep_db_input($year.sprintf('%02s',(int)$month))."' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='presel')
            {


                switch ($order_filter['interval']) {
                    case 'week':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('monday this week')) . "' ";
                        break;
                    case 'month':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('first day of this month')) . "' ";
                        break;
                    case 'year':
                        $filter_sql .= " AND o.date_purchased >= '" . date("Y") . "-01-01" . "' ";
                        break;
                    case '1':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d') . "' ";
                        break;
                    case '3':
                    case '7':
                    case '14':
                    case '30':
                        $filter_sql .= " AND o.date_purchased >= '".date('Y-m-d',strtotime('-'.$filters['interval'].' days'))."' ";
                        break;
                }
            }
        }
        $this->export_arrays = [];

        $main_sql =
            "SELECT o.orders_id ".
            "FROM ".TABLE_ORDERS." o ".
            "WHERE 1 {$filter_sql} ";
        $sql = tep_db_query( $main_sql );

        $this->export_query = tep_db_query( $main_sql );
        return $this->export_query;

    }

    public function exportRow()
    {
        $order_id = tep_db_fetch_array($this->export_query);
        if ( !is_array($order_id) ) return false;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        $order = new Order((int)$order_id['orders_id']);

        $this->data = array('data'=>$order);
        return $this->data;
    }
    public function importRow($data, Messages $message)
    {
        //print_r($message);

        $data = $data['result'];
        $sql_data_array = array(
            'customers_id' => 0,
            'basket_id' => 0,
            'customers_name' => $data['Ship-to_Firstname'] . ' ' . $data['Ship-to_Lastname'],
            //{{ BEGIN FISTNAME
            'customers_firstname' => $data['Ship-to_Firstname'],
            'customers_lastname' => $data['Ship-to_Lastname'],
            //}} END FIRSTNAME
            'customers_company' => '',
            'customers_company_vat' => '',
            'customers_street_address' => $data['Ship-to_Street'],
            'customers_suburb' => '',
            'customers_city' => $data['Ship-to_City'],
            'customers_postcode' => $data['Ship-to_Postcode'],
            'customers_state' => '',
            'customers_country' => $data['Ship-to_Country'],
            'customers_telephone' => $data['Ship-to_Telephone_Mobile'],
            'customers_landline' => $data['Ship-to_Telephone'],
            'customers_email_address' => '',
            'customers_address_format_id' => 0,
            'delivery_address_book_id' => 0,
            'delivery_gender' => '',
            'delivery_name' => $data['Ship-to_Firstname'] . ' ' . $data['Ship-to_Lastname'],
            //{{ BEGIN FISTNAME
            'delivery_firstname' => $data['Ship-to_Firstname'],
            'delivery_lastname' => $data['Ship-to_Lastname'],
            //}} END FIRSTNAME
            'delivery_street_address' => $data['Ship-to_Street'],
            'delivery_suburb' => '',
            'delivery_city' => $data['Ship-to_City'],
            'delivery_postcode' => $data['Ship-to_Postcode'],
            'delivery_state' => '',
            'delivery_country' => $data['Ship-to_Country'],
            'delivery_address_format_id' => 0,
            'billing_address_book_id' => 0,
            'billing_gender' => '',
            'billing_name' => $data['Ship-to_Firstname'] . ' ' . $data['Ship-to_Lastname'],
            //{{ BEGIN FISTNAME
            'billing_firstname' => $data['Ship-to_Firstname'],
            'billing_lastname' => $data['Ship-to_Lastname'],
            //}} END FIRSTNAME
            'billing_street_address' => $data['Ship-to_Street'],
            'billing_suburb' => '',
            'billing_city' => $data['Ship-to_City'],
            'billing_postcode' => $data['Ship-to_Postcode'],
            'billing_state' => '',
            'billing_country' => $data['Ship-to_Country'],
            'billing_address_format_id' => 0,
            'platform_id' => 1,
            'payment_method' => '',
            // BOF: Lango Added for print order mod
            'payment_info' => '',
            // EOF: Lango Added for print order mod
            'language_id' => 1, //(int)$languages_id,
            'payment_class' => '',
            'shipping_class' => '',
            'shipping_method' => '',
            'date_purchased' => 'now()',
            'last_modified' => 'now()',
            /* start search engines statistics */
            'search_engines_id' => 0,
            'search_words_id' =>  0,
            /* end search engines statistics */
            'orders_status' => 100006,
            'currency' => 'EUR',
            'currency_value' => 1,
            'shipping_weight' => 0,
            'adjusted' => 0,
            'reference_id' => 0,
            'delivery_date' =>$this->info['delivery_date'],
        );


        tep_db_perform(TABLE_ORDERS, $sql_data_array);
        $order_id = (int)tep_db_insert_id();
        if($order_id<0){
            return false;
        }
        $total = 0.0;

        if(is_array($data['OrderLine'])){

            if(!(count($data['OrderLine'], COUNT_RECURSIVE) - count($data['OrderLine'])) ){
                $tmp = $data['OrderLine'];
                $data['OrderLine'] = array();
                $data['OrderLine'][0]= $tmp;
                unset($tmp);
            }

            foreach ($data['OrderLine'] as $product) {
                $total = $total + round(floatval($product['Unit_Price'])*floatval($product['Quantity']),2);

                $sql = "select * from " . TABLE_PRODUCTS . " as p LEFT JOIN ". TABLE_PRODUCTS_DESCRIPTION." as pd ON pd.products_id=p.products_id where products_model = '".$product['Item'] ."' or products_model = '".$product['Item'] ."' LIMIT 1";
                $query = tep_db_query($sql);
                $row = false;
                if (tep_db_num_rows($query)) {
                    $row = tep_db_fetch_array($query);
                }

                $sql_data_array = array('orders_id' => $order_id,
                                        'products_quantity' => $product['Quantity'],
                                        'products_price' => round($product['Unit_Price'],2),
                                        'final_price' => round($product['Unit_Price'],2),
                                        'products_tax' => 0,
                                        'is_giveaway' => 0,
                                        'gift_wrap_price' => 0,
                                        'gift_wrapped' =>  0,
                                        'gv_state' => 'none',
                                        /* PC configurator addon begin */
                                        'template_uprid' => '',
                                        'parent_product' => '',
                                        'sub_products' => '',
                                        /* PC configurator addon end */
                                        'overwritten' => ''
                );


                if($row !== false){
                    $sql_data_array_add = array(
                        'products_id' => $row['products_id'],
                        'products_model' => $row['products_model'],
                        'products_name' => $row['products_name'],
                        'uprid' => \common\helpers\Inventory::normalize_id($row['products_id']),
                        'is_virtual' => $row['is_virtual'],
                    );
                }else{
                    $sql_data_array_add = array(
                        'products_id' => 0,
                        'products_model' => $product['Item'],
                        'products_name' => $product['Item'],
                        'uprid' => 0,
                        'is_virtual' => 0,
                    );
                }
                $sql_data_array = array_merge($sql_data_array_add, $sql_data_array);
                tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

            }
            $currencies = new \common\classes\Currencies();
            $sql_data_array = array(
                'orders_id' => $order_id,
                'title' => 'Total:',
                'text' => $currencies->format($total, true, 'EUR', 1),
                'value' => $total,
                'class' => 'ot_total',
                'sort_order' => 120,
                'text_exc_tax' => $currencies->format($total, true, 'EUR', 1),
                'text_inc_tax' => $currencies->format($total, true, 'EUR', 1),
                'tax_class_id' => 0,
                'value_exc_vat' => $total,
                'value_inc_tax' => $total,
                'is_removed' => 0,
                'currency' => 'EUR',
                'currency_value' => 1,
            );
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

            $sql_data_array = array('orders_id' => $order_id,
                                    'orders_status_id' => 100006,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => 'Imported Order');

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        return true;
    }
    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' orders');
        $message->info('Done.');

        $this->EPtools->done('orders_import');
    }

}
