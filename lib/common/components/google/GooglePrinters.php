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

namespace common\components\google;

use Yii;
use Google\Google_Client;

class GooglePrinters {

    private $client;
    private $httpClient;
    private $privacy_key = null;
    private $source;
    private static $scopes = ['https://www.googleapis.com/auth/cloudprint'];
    private $errorCode;
    private $errorMessage;

    public function __construct($config_file) {
        try {
            $this->privacy_key = json_decode(file_get_contents($config_file), true);
        } catch (\Exception $ex) {
            throw new \Exception('Configuration file ' . basename($config_file) . ' could not be loaded');
        }
        try {
            $this->client = new Google_Client();
            $this->client->setApplicationName("Google Printers");
            $this->client->useApplicationDefaultCredentials();

            $this->client->setAuthConfig($this->privacy_key);
            $this->client->addScope(self::$scopes);            
            $this->httpClient = $this->client->authorize();
            
        } catch (\Exception $ex) {
            $this->errorMessage = $ex->getMessage();
        }
        //echo '<pre>';var_dump($this->httpClient);die;
    }
    
    public function getError(){
        return [
            'errorCode'  => $this->errorCode,
            'message' => $this->errorMessage
        ];
    }
    
    public function invalidClient(){
        $this->errorCode = 400;
        $this->errorMessage = 'Invalid Request';
        return false;
    }

    /**
     * Return printers list
     * @return array with iDs as key
     */
    public function searchPrinters(){
        $params = array('type' => 'DOCS');
        if (!is_object($this->httpClient)){
            return $this->invalidClient();
        }
        $response = $this->httpClient->post('https://www.google.com/cloudprint/search', ['form_params' => $params]);
        $response = json_decode($response->getBody(), true);
        if ($response['errorCode']){
            $this->errorCode = $response['errorCode'];
            $this->errorMessage = $response['message'];
            return false;
        } else {
            return $this->representPrinters($response['printers']);
        }
    }
    
    public function representPrinters($printers){
        if (is_array($printers)){
            $tmp = [];
            foreach($printers as $printer){
                if ($printer['type'] == 'DOCS') continue;
                $tmp[$printer['id']] = $printer['name'] . " (" . $printer['connectionStatus'] . ")";
            }
            $printers = $tmp;
        }
        return $printers;
    }
    
    /**    
     * Get Printer Params by Cloud Pinter Id
     * @param type $id
     * @return array params| false
     */
    public function getPrinterDescription($id){
        $printer = $this->getPrinter($id);
        if ($printer){
            return $this->descibePrinter($printer);
        }
        return false;
    }
    
    protected function descibePrinter($cloudPrinter){
        $data = [
            'orient' => [],
            'formats' => [],
            'dpi' => [],
        ];
        if (is_array($cloudPrinter)){
            if (is_array($cloudPrinter['capabilities']['printer'])){
                $info = $cloudPrinter['capabilities']['printer'];
                if (is_array($info['page_orientation']['option'])){
                    foreach($info['page_orientation']['option'] as $option){
                        $data['orient'][] = $option['type'];
                    }
                }
                if (is_array($info['media_size']['option'])){
                    foreach($info['media_size']['option'] as $option){
                        $data['formats'][] = $option['custom_display_name'] . ' (' . ceil($option['width_microns'] /1000) . 'x' . ceil($option['height_microns'] /1000). ' mm)';
                    }
                }
                if (is_array($info['color']['option'])){
                    foreach($info['color']['option'] as $option){
                        $data['dpi'][] = $option['type'];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * GetPrinter by unique ID
     * @param type $id
     * @return printer details| false
     */
    public function getPrinter($id){
        $params = array('printerid' => $id);
        $response = $this->httpClient->post('https://www.google.com/cloudprint/printer', ['form_params' => $params]);
        $response = json_decode($response->getBody(), true);
        if ($response['errorCode']){
            $this->errorCode = $response['errorCode'];
            $this->errorMessage = $response['message'];
            return false;
        } else {
            return $response['printers'][0];
        }
    }
    
    /**
     * accept inviting printer for service account
     * @param type $id - printerId
     * @return type
     */
    public function getPrinterInvite($id){
        $params = array('printerid' => $id, 'accept' => true);
        $response = $this->httpClient->post('https://www.google.com/cloudprint/processinvite', ['form_params' => $params]);
        return json_decode($response->getBody(), true);
    }
    
    public function getJobs($id){
        $params = array('printerid' => $id);
        $response = $this->httpClient->post('https://www.google.com/cloudprint/jobs', ['form_params' => $params]);
        $response = json_decode($response->getBody(), true);
        if ($response['errorCode']){
            $this->errorCode = $response['errorCode'];
            $this->errorMessage = $response['message'];
            return false;
        } else {
            return $response['jobs'];
        }
    }

    private $job;
    public function createJob($id){
        $this->job = new GooglePrinterJob($id);
        return $this->job;
    }
    
    /**
     * submit job to printer
     * @param type $content
     * @return reponse|boolean
     */
    public function processJob($content){
        if (!$this->job) throw new \Exception('Job is not defined');
        $params = array('printerid' => $this->job->getPrinterId(), 'title' => $this->job->getTitle(), 'ticket' => $this->job->getTicket(), 'content' => $content);
        if ($type = $this->job->getContentType()){
            $params['contentType'] = $type;
        }
        $response = $this->httpClient->post('https://www.google.com/cloudprint/submit', ['form_params' => $params]);
        if ($response->getStatusCode() == 200){
            $response = json_decode($response->getBody(), true);
            if ($response['job']){
                $this->job->setLastJob($response['job']);
            }
            return $response;
        } else {
            $this->errorCode = $response->getStatusCode();
            $this->errorMessage = $response->getReasonPhrase();
            return false;
        }
    }
    
    public function getLastJob(){
        if (is_object($this->job)){
            return $this->job->getLastJob();
        }
        return false;
    }
}
