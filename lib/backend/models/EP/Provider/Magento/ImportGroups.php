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
use common\api\models\AR\Group;
use common\classes\language;
use backend\models\EP\Provider\Magento\helpers\SoapClient;
use backend\models\EP\Directory;

class ImportGroups implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $groups_list = [];
    protected $config = [];
    protected $afterProcessFilename = '';
    protected $afterProcessFile = false;
    protected $client;

    function __construct($config) {
        if (substr($config['client']['location'], -1) == '/'){
            $config['client']['location'] = substr($config['client']['location'], 0, -1);
        }            
        $this->config = $config;
    }
    
    public function allowRunInPopup(){
        return true;
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
        //$key = "jkajsdhfajfg&^jsaji0123";
        $mg = new SoapClient($this->config['client']);
        $this->client = $mg->getClient();
        $this->session = $mg->loginClient();

        $this->config['assign_platform'] = \common\classes\platform::defaultId();

        $this->getGroupList();

        $this->total_count = count($this->groups_list);

        $this->afterProcessFilename = tempnam($this->config['workingDirectory'], 'after_process');
        $this->afterProcessFile = fopen($this->afterProcessFilename, 'w+');
    }

    public function getGroupList() {
        try {
            $result = $this->client->call($this->session, 'customer_group.list');
            if (is_array($result) && count($result)){
                (new Group())->deleteAll();
            }
            $this->groups_list = $result;
        } catch (\Exception $ex) {
            throw new \Exception('Download remote stores info error');
        }
        return $result;
    }  

    public function processRow(Messages $message) {
        $remoteGroup = current($this->groups_list);

        if (!$remoteGroup)
            return false;

        $this->processRemoteGroup($remoteGroup);

        $this->row_count++;
        next($this->groups_list);
        return true;
    }


    public function postProcess(Messages $message) {
        return;
    }

    protected function processRemoteGroup($remoteGroup) {

        static $timing = [
            'soap' => 0,
            'local' => 0,
        ];
        $t1 = microtime(true);
        
        $group = new Group();
        
        if ($group){
            $t2 = microtime(true);
            $group->importArray($this->map($remoteGroup));
            if ($group->validate()){
                $group->save();
            }
        }
        
        $t3 = microtime(true);
        $timing['local'] += $t3 - $t2;        
    }
    
    public function map($data){
        return [
            'groups_id' => $data['customer_group_id'],
            'groups_name' => $data['customer_group_code'],
        ];
    }

}
