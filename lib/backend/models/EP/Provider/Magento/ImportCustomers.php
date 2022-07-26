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

namespace backend\models\EP\Provider\Magento;

use Yii;
use backend\models\EP\Exception;
use backend\models\EP\Messages;
use backend\models\EP\Provider\DatasourceInterface;
use backend\models\EP\Tools;
use common\classes\language;
use backend\models\EP\Provider\Magento\helpers\SoapClient;
use backend\models\EP\Provider\Magento\maps\CustomerMap;
use backend\models\EP\Directory;


class ImportCustomers implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $customers_list;
    protected $config = [];
    protected $afterProcessFilename = '';
    protected $afterProcessFile = false;
    protected $client;

    function __construct($config) {
        if (substr($config['client']['location'], -1) == '/'){
            $config['client']['location'] = substr($config['client']['location'], 0, -1);
        }            
        $this->config = $config;
        $this->initDB();
    }
    
    public function allowRunInPopup(){
        return true;
    }
    
    public function initDB() {
        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_customers(
   ep_directory_id INT(11) NOT NULL,
   remote_customers_id INT(11) NOT NULL,
   local_customers_id INT(11) NOT NULL,
   KEY(ep_directory_id, remote_customers_id),
   UNIQUE KEY(local_customers_id)
);");

        tep_db_query("CREATE TABLE IF NOT EXISTS ep_holbi_soap_link_addresses(
   ep_directory_id INT(11) NOT NULL,
   remote_address_id INT(11) NOT NULL,
   local_address_id INT(11) NOT NULL,
   KEY(ep_directory_id, remote_address_id),
   UNIQUE KEY(local_address_id)
);");
    }

    public function getProgress() {
        if ($this->total_count > 0) {
            $percentDone = min(100, ($this->row_count / $this->total_count) * 100);
        } else {
            $percentDone = 100;
        }
        return number_format($percentDone, 1, '.', '');
    }

    public function prepareProcess(Messages $message) {
        
        $mg = new SoapClient($this->config['client']);
        $this->client = $mg->getClient();
        $this->session = $mg->loginClient();

        $this->config['assign_platform'] = \common\classes\platform::defaultId();

        $this->getCustomersList();

        $this->total_count = count($this->customers_list);

        $this->afterProcessFilename = tempnam($this->config['workingDirectory'], 'after_process');
        $this->afterProcessFile = fopen($this->afterProcessFilename, 'w+');
    }

    public function getCustomersList() {
        try {
            $this->customers_list = $this->client->call($this->session, 'customer.list');
            if (is_array($this->customers_list)) {
                if (isset($this->config['trunkate_customers'])) {
                    \common\helpers\Customer::trunk_customers();
                    tep_db_query("delete from ep_holbi_soap_link_customers where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
                    tep_db_query("delete from ep_holbi_soap_link_addresses where ep_directory_id = '" . (int) $this->config['directoryId'] . "'");
                }    
            }
            //echo '<pre>';print_r($this->customers_list);die;
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customers list error');
        }
    }


    public function getCustomerInfo($id) {
        try {
            $result = $this->client->call($this->session, 'customer.info', $id);
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customer ' . $id . ' info error');
        }
        return $result;
    }
    
    public function getCustomerAddressInfo($id) {
        try {
            $result = $this->client->call($this->session, 'customer_address.info', $id);
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customer address info error');
        }
        return $result;
    }
    
    public function getCustomerAddresses($id){
        try {
            $result = $this->client->call($this->session, 'customer_address.list', $id);
            if ($result){
                if (is_array($result)){
                    foreach($result as $key => $address){
                         $result[$key] = array_merge($result[$key], $this->getCustomerAddressInfo($address['customer_address_id']));
                    }
                }               
            }
        } catch (\Exception $ex) {
            throw new \Exception('Fetch customer ' . $id . ' info error');
        }
        return $result;   
    }

    public function processRow(Messages $message) {
        $remoteCustomer = current($this->customers_list);
        if (!$remoteCustomer)
            return false;
        try{
            $this->processRemoteCustomer($remoteCustomer['customer_id'], true);
        } catch (\Exception $ex) {
            throw new \Exception('Processing customer error (' . $remoteCustomer['email'] . ')');
        }
        

        $this->row_count++;
        next($this->customers_list);
        return true;
    }


    public function postProcess(Messages $message) {
        return;
    }

    protected function processRemoteCustomer($remoteCustomerId) {

        static $timing = [
            'soap' => 0,
            'local' => 0,
        ];
        $t1 = microtime(true);

        $localId = $this->lookupLocalId($remoteCustomerId);
        if (!$localId){
        
            $remoteCustomer = $this->getCustomerInfo($remoteCustomerId);        
            $t2 = microtime(true);
            $timing['soap'] += $t2 - $t1;

            if ($remoteCustomer) {
                $remoteCustomer['addresses'] = $this->getCustomerAddresses($remoteCustomerId);
                
                $importArray = $this->map($remoteCustomer);
                
                if (!is_array($importArray) || !count($importArray)) return false;
                               
                $importArray['platform_id'] = $this->config['assign_platform'];
                $importArray['customers_status'] = 1;
                $localId = false;
                try{
                    $localCustomer = \common\api\models\AR\Customer::find()->where(['customers_email_address' => $remoteCustomer['email']])->orderBy('opc_temp_account')->one();
                } catch (\Exception $ex) {
                    echo '<pre>';print_r($ex);
                }
                
                if(!$localCustomer){
                    $localCustomer = new \common\api\models\AR\Customer();
                }
                
                try{
                    $localCustomer->importArray($importArray);
                } catch (\Exception $ex) {
                    echo '<pre>';print_r($ex);
                }
                
                if ($localCustomer->validate()) {
                    try{
                        $localCustomer->save(false);
                    } catch (\Exception $ex) {
                        echo $ex->getMessage();
                    }
                    
                    $localId = $localCustomer->customers_id;
                    $this->linkRemoteWithLocalId($remoteCustomerId, $localId);
                    $this->linkRemoteAddressWithLocal($localCustomer->initCollectionByLookupKey_Addresses(null));
                    if($localId){
                        if(isset($remoteCustomer['addresses'][0]['telephone']) && !empty($remoteCustomer['addresses'][0]['telephone'])){
                            $localCustomer->setAttribute('customers_telephone', $remoteCustomer['addresses'][0]['telephone']);
                            $localCustomer->update();
                        }
                    }
                    //echo '<pre>!!! '; var_dump($localId); echo '</pre>';
                }
                unset($localCustomer);
            }
        }
        $t3 = microtime(true);
        $timing['local'] += $t3 - $t2;
        //echo '<pre>';  var_dump($timing);    echo '</pre>';
    }
    
    protected function linkRemoteAddressWithLocal($addresses){
        if (is_array($addresses)){
            foreach ($addresses as $address ){
                if ($address instanceof \common\api\models\AR\Customer\Address && $address->save_lookup){
                    $localAbId = $address->getAttribute('address_book_id');
                    tep_db_query(
                            "INSERT INTO ep_holbi_soap_link_addresses(ep_directory_id, remote_address_id, local_address_id ) " .
                            " VALUES " .
                            " ('" . (int) $this->config['directoryId'] . "', '" . $address->save_lookup . "','" . $localAbId . "') " .
                            "ON DUPLICATE KEY UPDATE ep_directory_id='" . (int) $this->config['directoryId'] . "', remote_address_id='" . $address->save_lookup . "'"
                    );
                }
            }
        }        
    }

    protected function lookupLocalId($remoteId) {
        $get_local_id_r = tep_db_query(
                "SELECT local_customers_id " .
                "FROM ep_holbi_soap_link_customers " .
                "WHERE ep_directory_id='" . (int) $this->config['directoryId'] . "' " .
                " AND remote_customers_id='" . $remoteId . "'"
        );
        if (tep_db_num_rows($get_local_id_r) > 0) {
            $_local_id = tep_db_fetch_array($get_local_id_r);
            tep_db_free_result($get_local_id_r);
            return $_local_id['local_customers_id'];
        }
        return false;
    }

    protected function linkRemoteWithLocalId($remoteId, $localId) {
        tep_db_query(
                "INSERT INTO ep_holbi_soap_link_customers(ep_directory_id, remote_customers_id, local_customers_id ) " .
                " VALUES " .
                " ('" . (int) $this->config['directoryId'] . "', '" . $remoteId . "','" . $localId . "') " .
                "ON DUPLICATE KEY UPDATE ep_directory_id='" . (int) $this->config['directoryId'] . "', remote_customers_id='" . $remoteId . "'"
        );
        return true;
    }

    protected function map($responseObject) {
        $customer = json_decode(json_encode($responseObject), true); 
        
        $t1 = microtime(true);
        $simple = [];
        $simple = CustomerMap::apllyMapping($customer);
       
        $simple['customer_id'] = $customer['customer_id'];
        //echo '<pre>';print_r($simple);
        return $simple;
    }

}
