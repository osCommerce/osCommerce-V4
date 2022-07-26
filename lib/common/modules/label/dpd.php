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

namespace common\modules\label;

use common\classes\modules\ModuleLabel;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use Yii;
use \common\models;
use \common\helpers;

require_once 'dpd/DpdApi.php';

class dpd extends ModuleLabel
{
    public $title;
    public $description;
    public $code = 'dpd';
    
    public $can_update_shipment = false;
    public $can_cancel_shipment = false;
    
    private $_API = null;
    
    public function __construct() {
        $this->title = 'DPD';//MODULE_LABEL_DPD_TEXT_TITLE;
        $this->description = 'DPD';//MODULE_LABEL_DPD_TEXT_DESCRIPTION;
        $this->_API = new \DpdApi();
    }
    
    public function configure_keys()
    {
        return array (
        'MODULE_LABEL_DPD_STATUS' =>
          array (
            'title' => 'Enable DPD Labels',
            'value' => 'True',
            'description' => 'Do you want to offer DPD labels?',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
          ),
        'MODULE_LABEL_DPD_USERNAME' =>
          array (
            'title' => 'Username',
            'value' => '',
            'description' => 'Username',
            'sort_order' => '1',
          ),
        'MODULE_LABEL_DPD_PASSWORD' =>
          array (
            'title' => 'Password',
            'value' => '',
            'description' => 'Password',
            'sort_order' => '2',
          ),
        'MODULE_LABEL_DPD_SORT_ORDER' =>
          array (
            'title' => 'DPD Sort Order',
            'value' => '0',
            'description' => 'Sort order of display.',
            'sort_order' => '10',
          ),
      );
    }
    
    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_LABEL_DPD_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_LABEL_DPD_SORT_ORDER');
    }
    
    private function selectDpdAccount() {
        $config = [
            'username' => MODULE_LABEL_DPD_USERNAME,
            'password' => MODULE_LABEL_DPD_PASSWORD,
        ];
        $this->_API->setConfig($config);
    }
    
    /**
     * create shipment, get/set tracking
     * @param $orders_id
     * @return array
     */
    private function shipment_tracking($orders_id, $method = '') {
        $oOrder = models\Orders::findOne($orders_id);
        if ($oOrder->real_tracking_number == '') {
            $dpd_shipment = $this->insert_shipment($orders_id, $method);
            if (strlen($dpd_shipment['data']['shipmentId']) > 0) {
                $oOrder->real_tracking_number = $dpd_shipment['data']['shipmentId'];
                //$oOrder->tracking_number = $dpd_shipment['data']['shipmentId'];
                $oOrder->tracking_number = $dpd_shipment['data']['consignmentDetail'][0]['consignmentNumber'];
                //$oOrder->tracking_number = $dpd_shipment['data']['consignmentDetail'][0]['parcelNumbers'][0];

                try {
                    $label = $this->get_shipment_label($oOrder->real_tracking_number);
                    $oOrder->parcel_label_pdf = '1';
                    $oOrder->save();
// {{
                    global $login_id;
                    tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_id) values ('" . (int)$orders_id . "', '" . tep_db_input($oOrder->orders_status) . "', now(), '0', '" . tep_db_input(TEXT_TRACKING_NUMBER . ': ' . $oOrder->tracking_number)  . "', '" . tep_db_input($login_id)  . "')");
                    $check_old = tep_db_fetch_array(tep_db_query("select tracking_numbers_id from " . TABLE_TRACKING_NUMBERS . " where tracking_number = '" . tep_db_input($oOrder->tracking_number) . "' and orders_id = '" . (int) $orders_id . "'"));
                    if ( !($tracking_numbers_id > 0) ) {
                        tep_db_query("insert into " . TABLE_TRACKING_NUMBERS . " set orders_id = '" . (int) $orders_id . "', tracking_number = '" . tep_db_input($oOrder->tracking_number) . "'");
                        $tracking_numbers_id = tep_db_insert_id();
                        tep_db_query("delete from " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " where tracking_numbers_id = '" . (int) $tracking_numbers_id . "' and orders_id = '" . (int) $orders_id . "'");
                        $orders_products_query = tep_db_query("select orders_products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $orders_id . "'");
                        while ($orders_products = tep_db_fetch_array($orders_products_query)) {
                            tep_db_query("insert into " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " set tracking_numbers_id = '" . (int) $tracking_numbers_id . "', orders_id = '" . (int) $orders_id . "', orders_products_id = '" . (int) $orders_products['orders_products_id'] . "', products_quantity = '" . (int) $orders_products['products_quantity'] . "'");
                        }
                    }
// }}
                    tep_db_query("delete from " . TABLE_ORDERS_LABEL . " where orders_id = '" . (int) $orders_id . "'");
                    tep_db_query("insert into " . TABLE_ORDERS_LABEL . " set orders_id = '" . (int) $orders_id . "', parcel_label_pdf = '" . tep_db_input(base64_encode($label)) . "'");
                } catch (\Error $e) {
                    echo '<pre>';
                    print_r($e);
                    echo '</pre>';
                }

                return [
                    'exist' => 0,
                    'tracking_number' => $oOrder->tracking_number,
                    'real_tracking_number' => $oOrder->real_tracking_number,
                    'parcel_label' => $label,
                    'parcel_label_format' => 'vnd.eltron-epl'
                ];
            } elseif (is_array($dpd_shipment['error'])) {
                $return = [];
                foreach ($dpd_shipment['error'] as $error) {
                  $return['errors'][] = 'DPD: ' . $error['errorCode'] . ' - ' . $error['errorMessage'];
                }
                return $return;
            }
        } elseif ($oOrder->parcel_label_pdf == '') {
            try {
                $label = $this->get_shipment_label($oOrder->real_tracking_number);
                $oOrder->parcel_label_pdf = '1';
                $oOrder->save();
                tep_db_query("delete from " . TABLE_ORDERS_LABEL . " where orders_id = '" . (int) $orders_id . "'");
                tep_db_query("insert into " . TABLE_ORDERS_LABEL . " set orders_id = '" . (int) $orders_id . "', parcel_label_pdf = '" . tep_db_input(base64_encode($label)) . "'");
            } catch (\Error $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
            }
        }

        $check_label_order = tep_db_fetch_array(tep_db_query("select parcel_label_pdf from " . TABLE_ORDERS_LABEL . " where orders_id = '" . (int) $orders_id . "'"));
        $parcel_label_pdf = base64_decode($check_label_order['parcel_label_pdf']);

        return [
            'exist' => 1,
            'tracking_number' => $oOrder->tracking_number,
            'real_tracking_number' => $oOrder->real_tracking_number,
            'parcel_label' => $parcel_label_pdf,
            'parcel_label_format' => 'vnd.eltron-epl'
        ];
    }

    /**
     * get print label
     * @param $shipment_id
     */
    private function get_shipment_label($shipment_id) {
        return $this->_API->_send_request('/shipping/shipment/' . $shipment_id . '/label/', [], [], ['Accept: text/vnd.eltron-epl']);
    }

    /**
     * create DPD shipment
     * @param $orders_id
     * @return array|mixed|object
     */
    private function insert_shipment($orders_id, $method = '') {
        $order = new \common\classes\Order($orders_id);
        $this->selectDpdAccount();

        $config = Yii::$app->get('platform')->config($order->info['platform_id']);
        $platform = $config->getPlatformData();
        $platformsAddressBook = $config->getPlatformAddress();
        $platform_country = helpers\Country::get_country_info_by_id($platformsAddressBook['country_id']);

        $shipment = [
            'job_id' => null,
            'collectionOnDelivery' => false,
            'invoice' => null,
            'collectionDate' => $order->info['delivery_date'], // '2018-10-04T09:00:00',
            'consolidate' => true,
            'consignment' => [
                [
                    'consignmentNumber' => null,
                    'consignmentRef' => null,
                    'parcels' => [
                    ],
                    'collectionDetails' => [
                        'contactDetails' => [
                            'contactName' => $platform['platform_owner'],
                            'telephone' => $platform['platform_telephone'],
                        ],
                        'address' => [
                            'organisation' => $platformsAddressBook['company'],
                            'countryCode' => $platform_country['countries_iso_code_2'],
                            'postcode' => $platformsAddressBook['postcode'],
                            'street' => $platformsAddressBook['street_address'],
                            'locality' => $platformsAddressBook['suburb'],
                            'town' => $platformsAddressBook['city'],
                            'county' => $platformsAddressBook['state']
                        ]
                    ],
                    'deliveryDetails' => [
                        'contactDetails' => [
                            'contactName' => $order->delivery['name'] ? $order->delivery['name'] : $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                            'telephone' => $order->customer['telephone']
                        ],
                        'address' => [
                            'organisation' => $order->delivery['company'],
                            'countryCode' => $order->delivery['country']['iso_code_2'],
                            'postcode' => $order->delivery['postcode'],
                            'street' => $order->delivery['street_address'],
                            'locality' => $order->delivery['suburb'],
                            'town' => $order->delivery['city'],
                            'county' => $order->delivery['state']
                        ],
                        'notificationDetails' => [
                            'email' => $order->customer['email_address'],
                            'mobile' => ''
                        ]
                    ],
                    'networkCode' => $method,
                    'numberOfParcels' => 1,
                    'totalWeight' => ($order->info['shipping_weight'] > 0.1 ? $order->info['shipping_weight'] : 0.1),
                    'shippingRef1' => $orders_id,
                    'shippingRef2' => $platform['platform_name'],
                    'shippingRef3' => '',
                    'customsValue' => null,
                    'deliveryInstructions' => $order->info['comments'],
                    'parcelDescription' => '',
                    'liabilityValue' => null,
                    'liability' => false
                ]
            ]
        ];

        $json = '';
        try {
            $json = $this->_API->_send_request('/shipping/shipment/', [], json_encode($shipment), ['Accept: application/json', 'Content-Type: application/json']);
        } catch (\Exception $e) {
            
        }
        return json_decode($json, true);
    }

    /**
     * get services
     * @param $orders_id
     */
    private function get_services_list($orders_id) {
        $order = new \common\classes\Order($orders_id);
        $this->selectDpdAccount();

        $config = Yii::$app->get('platform')->config($order->info['platform_id']);
        $platformsAddressBook = $config->getPlatformAddress();
        $platform_country = helpers\Country::get_country_info_by_id($platformsAddressBook['country_id']);

        $get = [
            'collectionDetails.address.town' => $platformsAddressBook['city'],
            'collectionDetails.address.postcode' => $platformsAddressBook['postcode'],
            'collectionDetails.address.countryCode' => $platform_country['countries_iso_code_2'],
            'deliveryDetails.address.town' => $order->delivery['city'],
            'deliveryDetails.address.postcode' => $order->delivery['postcode'],
            'deliveryDetails.address.countryCode' => $order->delivery['country']['iso_code_2'],
            'deliveryDirection' => '1',
            'numberOfParcels' => '1',
            'totalWeight' => ($order->info['shipping_weight'] > 0.1 ? $order->info['shipping_weight'] : 0.1),
            'shipmentType' => '0',
        ];

        $result = $this->_API->_send_request('/shipping/network/', $get, [], ['Accept: application/json']);
        $services = json_decode($result, true);

        $methods = [];
        if (is_array($services['data'])) {
            foreach ($services['data'] as $service) {
                $methods[$this->code . '_' . $service['network']['networkCode']] = $service['network']['networkDescription'];
            }
        } elseif (is_array($services['error'])) {
            echo '<pre>';
            print_r($services['error']);
            echo '</pre>';
        }
        return $methods;
    }

    /**
     * check delivery date
     * @param type $delivery_date
     * @return boolean
     */
    public function checkDeliveryDate($delivery_date) {
        if (tep_not_null($delivery_date) && $delivery_date != '0000-00-00')
            return true;
        return false;
    }

    /**
     * get methods
     * @return mixed
     */
    public function get_methods($country_iso_code_2, $method = '', $shipping_weight = 0, $num_of_sheets = 0) {
        $orders_id = \Yii::$app->request->get('orders_id', 0);
        $methods = $this->get_services_list($orders_id);
        return $methods;
    }

    /**
     * @param $order_id
     * @param string $method
     * @return array
     */
    public function create_shipment($order_id, $method = '') {
        return $this->shipment_tracking($order_id, $method);
    }

    /**
     * cancel DPD order
     * @param $orders_id
     * @return array
     */
    public function cancel_shipment($orders_id) {
        return ['errors' => [
                'Order cannot be cancelled'
        ]];
    }

    /**
     * @param $order_id
     * @return array
     */
    public function update_shipment($order_id) {
        return ['errors' => [
                'Order cannot be updated'
        ]];
    }

}