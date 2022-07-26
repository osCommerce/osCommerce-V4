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

namespace backend\models\EP\Datasource;

use backend\models\EP\DatasourceBase;
use backend\models\EP\DataSources;
use backend\models\EP\Directory;
use backend\models\EP\Provider\HolbiSoap\Helper;

class RestClient extends DatasourceBase
{

    protected $remoteData = [];

    public function getName()
    {
        return 'REST Client';
    }

    public static function getProviderList()
    {
        return [
            'HolbiSoap\\DownloadProducts' => [
                'group' => 'Holbi SOAP',
                'name' => 'Import products',
                'class' => 'Provider\\HolbiSoap\\DownloadProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\DownloadPriceAndStock' => [
                'group' => 'Holbi SOAP',
                'name' => 'Download Price And Stock',
                'class' => 'Provider\\HolbiSoap\\DownloadPriceAndStock',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\ExportOrders' => [
                'group' => 'Holbi SOAP',
                'name' => 'Export orders',
                'class' => 'Provider\\HolbiSoap\\ExportOrders',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\TrackOrders' => [
                'group' => 'Holbi SOAP',
                'name' => 'Track orders',
                'class' => 'Provider\\HolbiSoap\\TrackOrders',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\SynchronizeCustomers' => [
                'group' => 'Holbi SOAP',
                'name' => 'Synchronize Customers',
                'class' => 'Provider\\HolbiSoap\\SynchronizeCustomers',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\SynchronizeCatalog' => [
                'group' => 'Holbi SOAP',
                'name' => 'Synchronize Catalog',
                'class' => 'Provider\\HolbiSoap\\SynchronizeCatalog',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\SynchronizeProperties' => [
                'group' => 'Holbi SOAP',
                'name' => 'Synchronize Properties',
                'class' => 'Provider\\HolbiSoap\\SynchronizeProperties',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\SynchronizeBrands' => [
                'group' => 'Holbi SOAP',
                'name' => 'Synchronize Brands',
                'class' => 'Provider\\HolbiSoap\\SynchronizeBrands',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\UpdateProductOnServer' => [
                'group' => 'Holbi SOAP',
                'name' => 'Update products on server',
                'class' => 'Provider\\HolbiSoap\\UpdateProductOnServer',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\ExportToSap' => [
                'group' => 'Holbi SOAP',
                'name' => 'Manual export order to SAP',
                'class' => 'Provider\\HolbiSoap\\ExportToSap',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiSoap\\SynchronizePO' => [
                'group' => 'Holbi SOAP',
                'name' => 'Synchronize PurchaseOrders',
                'class' => 'Provider\\HolbiSoap\\SynchronizePO',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
        ];
    }


    public function prepareConfigForView($configArray)
    {
        $orderStatusesSelect = [
            '*' => '[Any order status]',
        ];
        foreach( \common\helpers\Order::getStatusesGrouped(true) as $option){
            $orderStatusesSelect[$option['id']] = html_entity_decode($option['text'],null,'UTF-8');
        }
        $configArray['order']['export_statuses'] = [
            'items' => $orderStatusesSelect,
            'value' => $configArray['order']['export_statuses'],
            'options' => [
                'class' => 'form-control',
                'multiple' => true,
                'options' => [
                ],
            ],
        ];

        $currentGroup = '';
        $items = [];
        foreach( \common\helpers\Order::getStatusesGrouped(true) as $orderStatusVariant ) {
            if ( strpos($orderStatusVariant['id'],'group_')===0 ) {
                $currentGroup = $orderStatusVariant['text'];
                continue;
            }
            $items[$currentGroup][str_replace('status_','',$orderStatusVariant['id'])]
                = str_replace('&nbsp;','',$orderStatusVariant['text']);
        }
        $configArray['order']['export_success_status'] = [
            'items' => $items,
            'value' => $configArray['order']['export_success_status'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];

        $configArray['order']['local_dispatch_status'] = [
            'items' => array_merge(['0'=>'Leave current status'], $items),
            'value' => $configArray['order']['local_dispatch_status'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];
        $this->initRemoteData($configArray);
        $configArray['status_map_local_to_server'] = $this->settings['status_map_local_to_server'];

        $configArray['order']['server_dispatched_statuses'] = [
            'fetched' => false,
            'items' => [],
            'value' => $configArray['order']['server_dispatched_statuses'],
            'options' => [
                'class' => 'form-control',
                'multiple' => true,
                'options' => [
                ],
            ],
        ];
        if (isset($this->remoteData['order_statuses'])) {
            $configArray['order']['server_dispatched_statuses']['fetched'] = true;
            $configArray['order']['server_dispatched_statuses']['items'] = array_merge($configArray['order']['server_dispatched_statuses']['items'],$this->remoteData['order_statuses']);
        }

        $configArray['StockIndicationVariants'] = [];
        foreach ( \common\classes\StockIndication::get_variants() as $variant) {
            $configArray['StockIndicationVariants'][$variant['id']] = $variant['text'];
        }

        $configArray['StockDeliveryTermsVariants'] = [];
        foreach ( \common\classes\StockIndication::get_delivery_terms() as $variant) {
            $configArray['StockDeliveryTermsVariants'][$variant['id']] = $variant['text'];
        }

        $configArray['ServerProductsRemovedVariants'] = [
            '' => 'No action',
            'remove' => 'Remove from catalog',
            'disable' => 'Set as inactive',
        ];

        $configArray['LocalShopOrderStatuses'] = \common\helpers\Order::getStatusesGrouped(true);

        //$configArray['ServerShopOrderStatuses'] = $this->remoteData['order_statuses_list'];
        $configArray['ServerShopOrderStatuses'] = [0=>''];
        if ( is_array($this->remoteData['order_statuses']) ) {
            $configArray['ServerShopOrderStatuses'] = array_merge($configArray['ServerShopOrderStatuses'], $this->remoteData['order_statuses']);
        }
        $configArray['ServerShopOrderStatusesWithCreate'] = [0=>''];
        if ( isset($this->remoteData['order_statuses_list']) && is_array($this->remoteData['order_statuses_list']) ) {
            foreach ($this->remoteData['order_statuses_list'] as $serverStatus) {

                if (strpos($serverStatus['id'], 'group') === 0) {
                    $group_name = $serverStatus['name'];
                    continue;
                }
                if (!isset($configArray['ServerShopOrderStatusesWithCreate'][$group_name])) {
                    $configArray['ServerShopOrderStatusesWithCreate'][$group_name]['create_in_' . $serverStatus['group_id']] = '[Create in group "' . $group_name . '"]';
                }
                $configArray['ServerShopOrderStatusesWithCreate'][$group_name][$serverStatus['_id']] = $serverStatus['name'];
            }
        }

        $configArray['order']['disable_order_update'] = [
            'items' => [
                '0'=>'Enabled',
                '1'=>'Disabled, accept server tracking number',
            ],
            'value' => $configArray['order']['disable_order_update'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];
        $configArray['order']['export_as'] = [
            'items' => [
                'order'=>'Order - Order',
                'po_order'=>'Order - Purchase Order',
            ],
            'value' => $configArray['order']['export_as'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];

        $configArray['customer']['ab_sync_server'] = [
            'items' => [
                'replace'=>'Replace',
                'append'=>'Append',
                'disable' => 'Disable customer synchronization'
            ],
            'value' => $configArray['customer']['ab_sync_server'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];
        $configArray['customer']['ab_sync_client'] = [
            'items' => [
                'replace'=>'Replace',
                'append'=>'Append',
                'disable' => 'Disable customer synchronization'
            ],
            'value' => $configArray['customer']['ab_sync_client'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];

        $configArray['products']['images_copy'] = [
            'items' => [
                'external'=>'Link (external images)',
                'copy'=>'Local copy',
            ],
            'value' => $configArray['products']['images_copy'],
            'options' => [
                'class' => 'form-control',
                'options' => [
                ],
            ],
        ];

        $configArray['products']['create_on_client'] = isset($configArray['products']['create_on_client'])?!!$configArray['products']['create_on_client']:true;
        $configArray['products']['create_on_server'] = isset($configArray['products']['create_on_server'])?!!$configArray['products']['create_on_server']:false;
        $configArray['products']['update_on_client'] = isset($configArray['products']['update_on_client'])?!!$configArray['products']['update_on_client']:true;
        $configArray['products']['update_on_server'] = isset($configArray['products']['update_on_server'])?!!$configArray['products']['update_on_server']:false;

        foreach ( static::productFlags() as $flagInfo ) {
            if ( $flagInfo['server'] && isset($configArray['products'][$flagInfo['server']])) {
                $configArray['products']['custom_flags'] = true;
                break;
            }
            if ( $flagInfo['client'] && isset($configArray['products'][$flagInfo['client']])) {
                $configArray['products']['custom_flags'] = true;
                break;
            }
        }

        return parent::prepareConfigForView($configArray);
    }


    public function getViewTemplate()
    {
        return 'datasource/holbi-soap.tpl';
    }

    static public function beforeSettingSave($data)
    {
        $data['products']['create_on_client'] = isset($data['products']['create_on_client'])?!!$data['products']['create_on_client']:false;
        $data['products']['create_on_server'] = isset($data['products']['create_on_server'])?!!$data['products']['create_on_server']:false;
        $data['products']['update_on_client'] = isset($data['products']['update_on_client'])?!!$data['products']['update_on_client']:false;
        $data['products']['update_on_server'] = isset($data['products']['update_on_server'])?!!$data['products']['update_on_server']:false;

        if (isset($data['products']['custom_flags'])) {
            unset($data['products']['custom_flags']);
            foreach ( static::productFlags() as $flagInfo ) {
                if ( $flagInfo['select'] ) {
                    if ($flagInfo['server']) $data['products'][$flagInfo['server']] = isset($data['products'][$flagInfo['server']]) ? $data['products'][$flagInfo['server']] : current(array_keys($flagInfo['select']));
                    if ($flagInfo['client']) $data['products'][$flagInfo['client']] = isset($data['products'][$flagInfo['client']]) ? $data['products'][$flagInfo['client']] : current(array_keys($flagInfo['select']));
                }else {
                    if ($flagInfo['server']) $data['products'][$flagInfo['server']] = isset($data['products'][$flagInfo['server']]) ? !!$data['products'][$flagInfo['server']] : false;
                    if ($flagInfo['client']) $data['products'][$flagInfo['client']] = isset($data['products'][$flagInfo['client']]) ? !!$data['products'][$flagInfo['client']] : false;
                }
            }
        }else{
            foreach ( static::productFlags() as $flagInfo ) {
                if ( $flagInfo['server'] ) unset($data['products'][$flagInfo['server']]);
                if ( $flagInfo['client'] ) unset($data['products'][$flagInfo['client']]);
            }
        }

        $data = parent::beforeSettingSave($data);
        $class = '\\' . self::className();
        //$datasource = new self();

        $datasource = new $class;
        $client = $datasource->getClient($data['client']);
        if ($client ) {
          try {
              $response = $client->getServerTime();
          }catch (\Exception $e){
            if (method_exists($client, '__getLastRequestHeaders')) {
              \Yii::error("Datasource Config: ".$client->__getLastRequestHeaders()."\n".$client->__getLastRequest()."\n".$client->__getLastResponseHeaders()."\n".$client->__getLastResponse()."\n",'datasource');
            } else {
              \Yii::error("Datasource Config: " . print_r($e, 1), 'datasource');
            }
            throw $e;
          }
        }
        if ($response && $response->time) {
            // ok
        }
        if ($data['status_map']!=1){

        }

        return $data;
    }

    public function update($settings)
    {
        parent::update($settings);
        try{

            $config = $this->getJobConfig();
            $client = $this->getClient($config['client']);

            Helper::putOrderStatusesOnServer($client, $config);
            if ( isset($config['status_map_local_to_server']) && preg_grep('/^create_on/',$config['status_map_local_to_server']) ) {
                Helper::syncOrderStatuses($client, $this);
            }


        }catch (\Exception $ex){
        }
    }

    public function getClient($clientConfig)
    {
        if ( !is_array($clientConfig) ) {
            $clientConfig = $this->settings['client'];
        }
        $client = false;
        if ( isset($clientConfig['wsdl_location']) && !empty($clientConfig['wsdl_location']) ) {
            if ($ch = @curl_init($clientConfig['wsdl_location'])) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $cc = @curl_exec($ch);
                if (!$cc) {
                    throw new \InvalidArgumentException(curl_error($ch));
                } elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) > 300) {
                    throw new \InvalidArgumentException('Check API WSDL URL');
                }
                curl_close($ch);
            }

            if ( isset($clientConfig['department_api_key']) && !empty($clientConfig['department_api_key']) ) {
                try {
                    $client = new \SoapClient(
                        $clientConfig['wsdl_location'],
                        [
                            'trace' => 1,
                            //'proxy_host'     => "localhost",
                            //'proxy_port'     => 8080,
                            //'proxy_login'    => "some_name",
                            //'proxy_password' => "some_password",
                            'cache_wsdl' => WSDL_CACHE_NONE,
                            'connection_timeout' => 10,
                            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                            'stream_context' => stream_context_create([
                                'http' => [
                                    //'header'  => "APIToken: $api_token\r\n",
                                ]
                            ]),
                        ]
                    );
                    $auth = new \stdClass();
                    $auth->api_key = $clientConfig['department_api_key'];
                    $soapHeaders = new \SoapHeader('http://schemas.xmlsoap.org/ws/2002/07/utility', 'auth', $auth, false);
                    $client->__setSoapHeaders($soapHeaders);
                } catch (\Exception $ex) {
                    throw new \InvalidArgumentException($ex->getMessage());
                }
            }
        }
        return $client;
    }

    public function initRemoteData($configArray)
    {
        try{
            $client = $this->getClient($configArray['client']);
            if ( $client ) {
                Helper::syncOrderStatuses($client, $this);
                $serverStatuses = Helper::getOrderStatusesFromServer($client);
                if (is_array($serverStatuses)) {
                    $this->remoteData['order_statuses'] = [];
                    $this->remoteData['order_statuses_list'] = [];

                    $this->remoteData['order_statuses_list'] = $serverStatuses;
                    $group_name = '';

                    foreach ($serverStatuses as $serverStatus) {
                        if (strpos($serverStatus['id'], 'group') === 0) {
                            $group_name = $serverStatus['name'];
                            continue;
                        }
                        $this->remoteData['order_statuses'][$group_name][$serverStatus['_id']] = $serverStatus['name'];
                    }
                }
            }
        }catch (\Exception $ex){

        }
    }

    public static function productFlags()
    {
        return [
            [
                'label' => 'Name and description',
                'server' => 'description_server',
                'client' => 'description_client',
            ],
            [
                'label' => 'SEO',
                'server' => 'seo_server',
                'client' => 'seo_client',
            ],
            [
                'label' => 'Prices',
                'server' => 'prices_server',
                'client' => 'prices_client',
                //'only_one_active' => true,
                'select' => [
                    'disabled' => 'Disable update',
                    'as_is' => 'Price to price',
                    //'to_supplier' => 'Price to supplier',
                ],
            ],
            [
                'label' => 'Stock',
                'server' => 'stock_server',
                'client' => 'stock_client',
                'only_one_active' => true,
                'select' => [
                    'disabled' => 'Disable update',
                    'as_is' => 'Stock to Stock',
                    //'to_supplier' => 'Stock to supplier',
                ]
            ],
            [
                'label' => 'Attributes and inventory',
                'server' => 'attr_server',
                'client' => 'attr_client',
            ],
            [
                'label' => 'Product identifiers',
                'server' => 'identifiers_server',
                'client' => 'identifiers_client',
            ],
            [
                'label' => 'Images',
                'server' => 'images_server',
                'client' => 'images_client',
            ],
            [
                'label' => 'Size and Dimensions',
                'server' => 'dimensions_server',
                'client' => 'dimensions_client',
            ],
            [
                'label' => 'Properties',
                'server' => 'properties_server',
                'client' => 'properties_client',
            ],
            [
                'label' => 'Documents',
                'server' => 'documents_server',
                'client' => 'documents_client',
            ],
        ];
    }

    public static function productEdit($pInfo)
    {
        $activeServers = DataSources::getActiveByClass('HolbiSoap');
        $directories = [];
        $tab_data = [];
        foreach ($activeServers as $id=>$datasource) {
            $directories[$id] = $datasource->code;
            $tab_data[$id] = [
                'imported_from_this_server' => false,
                'allow_send_to_server' => false,
            ];
        }

        if ( $pInfo->products_id && count($directories)>0 ) {
            $allPossibleCustomFlags = [];
            foreach (static::productFlags() as $flagInfo) {
                if ( isset($flagInfo['server']) ) $allPossibleCustomFlags[$flagInfo['server']] = $flagInfo['server'];
                if ( isset($flagInfo['client']) ) $allPossibleCustomFlags[$flagInfo['client']] = $flagInfo['client'];
            }

            $get_current_server_state_r = tep_db_query(
                "SELECT ep_directory_id, remote_products_id, local_products_id ".
                "FROM ep_holbi_soap_link_products ".
                "WHERE local_products_id='".$pInfo->products_id."' ".
                "  AND ep_directory_id IN('".implode("','",array_keys($directories))."')"
            );
            if ( tep_db_num_rows($get_current_server_state_r)>0 ) {
                while($_current_server_state = tep_db_fetch_array($get_current_server_state_r)) {
                    $tab_data[$_current_server_state['ep_directory_id']]['imported_from_this_server'] = true;
                }
            }
            foreach (array_keys($activeServers) as $directory_id) {
                $get_flags_r = tep_db_query(
                    "SELECT products_id, flag_name, flag_value " .
                    "FROM ep_holbi_soap_products_flags " .
                    "WHERE ep_directory_id='" . $directory_id . "' " .
                    " AND products_id IN(-1, " . (int)$pInfo->products_id . ") ".
                    "ORDER BY products_id"
                );
                if (tep_db_num_rows($get_flags_r) > 0) {
                    while ($get_flag = tep_db_fetch_array($get_flags_r)) {
                        if ( $get_flag['products_id']>0 && isset($allPossibleCustomFlags[$get_flag['flag_name']]) ) {
                            $tab_data[$directory_id]['custom_flags'] = 1;
                        }
                        if ( $get_flag['products_id']>0 && in_array($get_flag['flag_name'], ['create_on_server', 'update_on_client', 'update_on_server']) ) {
                            $tab_data[$directory_id]['datasource_main_flags'] = 1;
                        }
                        $tab_data[$directory_id][$get_flag['flag_name']] = $get_flag['flag_value'];
                    }
                }
            }
        }

        $pInfo->soap_config = [
            'directories' => $directories,
            'active_directory_id' => current(array_keys($directories)),
            'tab_data' => $tab_data,
        ];
    }

    public static function productUpdate($productsId, $soap_config)
    {
        $activeServers = DataSources::getActiveByClass('HolbiSoap');
        tep_db_query(
            "DELETE FROM ep_holbi_soap_products_flags ".
            "WHERE products_id='".$productsId."' ".
            (count($activeServers)>0?" AND ep_directory_id NOT IN('".implode("','",array_keys($activeServers))."')":'')
        );

        foreach (array_keys($activeServers) as $directory_id) {
            $useMainCustom = false;
            if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                $useMainCustom = (isset($soap_config[$directory_id]['datasource_main_flags']) && $soap_config[$directory_id]['datasource_main_flags']==1)?true:false;
            }
            if ( $useMainCustom ) {
                foreach (['create_on_server', 'update_on_client', 'update_on_server'] as $flag_name) {
                    $flag_value = 0;
                    if (isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id])) {
                        if (isset($soap_config[$directory_id][$flag_name]) && $soap_config[$directory_id][$flag_name] == 1) {
                            $flag_value = 1;
                        }
                    }

                    tep_db_query(
                        "INSERT INTO ep_holbi_soap_products_flags (ep_directory_id, products_id, flag_name, flag_value ) " .
                        "VALUES ('" . $directory_id . "', '" . (int)$productsId . "', '" . tep_db_input($flag_name) . "', '" . (int)$flag_value . "') " .
                        "ON DUPLICATE KEY UPDATE flag_value='" . (int)$flag_value . "' "
                    );
                }
            }else{
                tep_db_query(
                    "DELETE FROM ep_holbi_soap_products_flags ".
                    "WHERE ep_directory_id='" . $directory_id . "' AND products_id='" . (int)$productsId . "' ".
                    " AND flag_name IN ('create_on_server', 'update_on_client', 'update_on_server') "
                );
            }

            foreach (static::productFlags() as $flagInfo) {
                $useCustom = false;
                if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                    $useCustom = (isset($soap_config[$directory_id]['custom_flags']) && $soap_config[$directory_id]['custom_flags']==1)?true:false;
                }
                if ( isset($flagInfo['client']) ) {
                    $flag_name = $flagInfo['client'];
                    $flag_value = 0;
                    if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                        if (isset($soap_config[$directory_id][$flag_name])) {
                            if ( !isset($flagInfo['select']) && $soap_config[$directory_id][$flag_name]==1 ) {
                                $flag_value = 1;
                            }elseif(isset($flagInfo['select'])){
                                $flag_value = $soap_config[$directory_id][$flag_name];
                            }
                        }
                    }
                    if ( $useCustom ) {
                        tep_db_query(
                            "INSERT INTO ep_holbi_soap_products_flags (ep_directory_id, products_id, flag_name, flag_value ) " .
                            "VALUES ('" . $directory_id . "', '" . (int)$productsId . "', '" . tep_db_input($flag_name) . "', '" . tep_db_input($flag_value) . "') " .
                            "ON DUPLICATE KEY UPDATE flag_value='" . tep_db_input($flag_value) . "' "
                        );
                    }else{
                        tep_db_query(
                            "DELETE FROM ep_holbi_soap_products_flags ".
                            "WHERE ep_directory_id='" . $directory_id . "' AND products_id='" . (int)$productsId . "' ".
                            " AND flag_name='" . tep_db_input($flag_name) . "' "
                        );
                    }
                }
                if ( isset($flagInfo['server']) ) {
                    $flag_name = $flagInfo['server'];
                    $flag_value = 0;
                    if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                        if ( !isset($flagInfo['select']) && $soap_config[$directory_id][$flag_name]==1 ) {
                            $flag_value = 1;
                        }elseif(isset($flagInfo['select'])){
                            $flag_value = $soap_config[$directory_id][$flag_name];
                        }
                    }
                    if ( $useCustom ) {
                        tep_db_query(
                            "INSERT INTO ep_holbi_soap_products_flags (ep_directory_id, products_id, flag_name, flag_value ) ".
                            "VALUES ('".$directory_id."', '".(int)$productsId."', '".tep_db_input($flag_name)."', '".tep_db_input($flag_value)."') ".
                            "ON DUPLICATE KEY UPDATE flag_value='".tep_db_input($flag_value)."' "
                        );
                    }else{
                        tep_db_query(
                            "DELETE FROM ep_holbi_soap_products_flags ".
                            "WHERE ep_directory_id='" . $directory_id . "' AND products_id='" . (int)$productsId . "' ".
                            " AND flag_name='" . tep_db_input($flag_name) . "' "
                        );
                    }
                }
            }
        }
    }

    public static function categoryEdit($cInfo)
    {
        $activeServers = DataSources::getActiveByClass('HolbiSoap');
        $directories = [];
        $tab_data = [];
        foreach ($activeServers as $id=>$datasource) {
            $directories[$id] = $datasource->code;
            $tab_data[$id] = [
                'imported_from_this_server' => false,
                'allow_send_to_server' => false,
            ];
        }
        if ( $cInfo->categories_id && count($directories)>0 ) {
            $allPossibleCustomFlags = [];
            foreach (static::productFlags() as $flagInfo) {
                if ( isset($flagInfo['server']) ) $allPossibleCustomFlags[$flagInfo['server']] = $flagInfo['server'];
                if ( isset($flagInfo['client']) ) $allPossibleCustomFlags[$flagInfo['client']] = $flagInfo['client'];
            }

            foreach (array_keys($activeServers) as $directory_id) {
                $get_flags_r = tep_db_query(
                    "SELECT categories_id, flag_name, flag_value " .
                    "FROM ep_holbi_soap_category_products_flags " .
                    "WHERE ep_directory_id='" . $directory_id . "' " .
                    " AND categories_id IN(-1, " . (int)$cInfo->categories_id . ") ".
                    "ORDER BY categories_id"
                );
                if (tep_db_num_rows($get_flags_r) > 0) {
                    while ($get_flag = tep_db_fetch_array($get_flags_r)) {
                        if ( $get_flag['categories_id']>0 && isset($allPossibleCustomFlags[$get_flag['flag_name']]) ) {
                            $tab_data[$directory_id]['custom_flags'] = 1;
                        }
                        if ( $get_flag['categories_id']>0 && in_array($get_flag['flag_name'], ['create_on_server', 'update_on_client', 'update_on_server']) ) {
                            $tab_data[$directory_id]['datasource_main_flags'] = 1;
                        }
                        $tab_data[$directory_id][$get_flag['flag_name']] = $get_flag['flag_value'];
                    }
                }
            }
        }
        if ( count($directories)>0 ) {
            $cInfo->soap_config = [
                'directories' => $directories,
                'active_directory_id' => current(array_keys($directories)),
                'tab_data' => $tab_data,
            ];
        }
    }

    public static function categoryUpdate($categoryId, $soap_config)
    {
        $activeServers = DataSources::getActiveByClass('HolbiSoap');
        tep_db_query(
            "DELETE FROM ep_holbi_soap_category_products_flags ".
            "WHERE categories_id='".$categoryId."' ".
            (count($activeServers)>0?" AND ep_directory_id NOT IN('".implode("','",array_keys($activeServers))."')":'')
        );

        foreach (array_keys($activeServers) as $directory_id) {
            $useMainCustom = false;
            if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                $useMainCustom = (isset($soap_config[$directory_id]['datasource_main_flags']) && $soap_config[$directory_id]['datasource_main_flags']==1)?true:false;
            }
            if ( $useMainCustom ) {
                foreach (['create_on_server', 'update_on_client', 'update_on_server'] as $flag_name) {
                    $flag_value = 0;
                    if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                        if ( isset($soap_config[$directory_id][$flag_name]) && $soap_config[$directory_id][$flag_name]==1 ) {
                            $flag_value = 1;
                        }
                    }

                    tep_db_query(
                        "INSERT INTO ep_holbi_soap_category_products_flags (ep_directory_id, categories_id, flag_name, flag_value ) " .
                        "VALUES ('" . $directory_id . "', '" . (int)$categoryId . "', '" . tep_db_input($flag_name) . "', '" . tep_db_input($flag_value) . "') " .
                        "ON DUPLICATE KEY UPDATE flag_value='" . tep_db_input($flag_value) . "' "
                    );
                }
            }else{
                tep_db_query(
                    "DELETE FROM ep_holbi_soap_category_products_flags ".
                    "WHERE ep_directory_id='" . $directory_id . "' AND categories_id='" . (int)$categoryId . "' ".
                    " AND flag_name IN ('create_on_server', 'update_on_client', 'update_on_server') "
                );
            }

            foreach (static::productFlags() as $flagInfo) {
                $useCustom = false;
                if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                    $useCustom = (isset($soap_config[$directory_id]['custom_flags']) && $soap_config[$directory_id]['custom_flags']==1)?true:false;
                }
                if ( isset($flagInfo['client']) ) {
                    $flag_name = $flagInfo['client'];
                    $flag_value = 0;
                    if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                        if ( !isset($flagInfo['select']) && isset($soap_config[$directory_id][$flag_name]) && $soap_config[$directory_id][$flag_name]==1 ) {
                            $flag_value = 1;
                        }elseif(isset($flagInfo['select'])){
                            $flag_value = $soap_config[$directory_id][$flag_name];
                        }
                    }
                    if ( $useCustom ) {
                        tep_db_query(
                            "INSERT INTO ep_holbi_soap_category_products_flags (ep_directory_id, categories_id, flag_name, flag_value ) " .
                            "VALUES ('" . $directory_id . "', '" . (int)$categoryId . "', '" . tep_db_input($flag_name) . "', '" . tep_db_input($flag_value) . "') " .
                            "ON DUPLICATE KEY UPDATE flag_value='" . tep_db_input($flag_value) . "' "
                        );
                    }else{
                        tep_db_query(
                            "DELETE FROM ep_holbi_soap_category_products_flags ".
                            "WHERE ep_directory_id='" . $directory_id . "' AND categories_id='" . (int)$categoryId . "' ".
                            " AND flag_name='" . tep_db_input($flag_name) . "' "
                        );
                    }
                }
                if ( isset($flagInfo['server']) ) {
                    $flag_name = $flagInfo['server'];
                    $flag_value = 0;
                    if ( isset($soap_config[$directory_id]) && is_array($soap_config[$directory_id]) ) {
                        if ( !isset($flagInfo['select']) && isset($soap_config[$directory_id][$flag_name]) && $soap_config[$directory_id][$flag_name]==1 ) {
                            $flag_value = 1;
                        }elseif(isset($flagInfo['select'])){
                            $flag_value = $soap_config[$directory_id][$flag_name];
                        }
                    }
                    if ( $useCustom ) {
                        tep_db_query(
                            "INSERT INTO ep_holbi_soap_category_products_flags (ep_directory_id, categories_id, flag_name, flag_value ) ".
                            "VALUES ('".$directory_id."', '".(int)$categoryId."', '".tep_db_input($flag_name)."', '".tep_db_input($flag_value)."') ".
                            "ON DUPLICATE KEY UPDATE flag_value='".tep_db_input($flag_value)."' "
                        );
                    }else{
                        tep_db_query(
                            "DELETE FROM ep_holbi_soap_category_products_flags ".
                            "WHERE ep_directory_id='" . $directory_id . "' AND categories_id='" . (int)$categoryId . "' ".
                            " AND flag_name='" . tep_db_input($flag_name) . "' "
                        );
                    }
                }
            }
        }
    }
}
