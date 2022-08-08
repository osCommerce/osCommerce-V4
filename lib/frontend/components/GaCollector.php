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

namespace frontend\components;

use \common\models\repositories\GaRepository;

class GaCollector {
    
    private $repository;
    private $cookie;
    
    public function __construct(GaRepository $repository){
        $this->repository = $repository;
    }
    
    public function collectData(\common\services\OrderManager $manager){
        if ($manager->isCustomerAssigned()){
            $basketDetection = $this->repository->getBasketDetectedInstance($manager->getCustomerAssigned(), $manager->getCart()->basketID);
            $order_id = $order = null;
            if ($manager->isInstance()){
                $order = $manager->getOrderInstance();
                $order_id = $order->order_id;
            }
            $this->cookie = new \yii\web\CookieCollection($_COOKIE);
            $params = $this->_detectParams();
            $this->repository->updateInstance($basketDetection, $params, $order_id);
            
            if (class_exists('\common\components\GoogleTools') && is_object($order)) {
                \common\components\GoogleTools::instance()->checkOrderPosition($order);
            }
        }
    }
    
    protected function _detectParams(){
        $params = [];
        $ua = new \common\components\UserAgentParser();
        
        $params = [
            'user_agent' => serialize($ua->parse_user_agent()),
            'resolution' => $this->cookie->get('xwidth') ? $this->cookie->get('xwidth').'x'.$this->cookie->get('xheight'): '',
            'ip_address' => \common\helpers\System::get_ip_address(),
        ];
        
        $this->addGclId($params)
            ->addClientId($params)
            ->addUtmzParams($params);
        
        return $params;
    }
    
    protected function addGclId(&$params){
        foreach($this->cookie->getIterator() as $key => $value){
            preg_match("/_gac_UA.*/", $key, $match);
            if (is_array($match) && count($match) > 1 && $match[0]){
                $value = preg_match("/^[\d]{1,2}\.[\d]*\.(.*)$/", $value, $match);
                if (is_array($match)&& $match[1]){
                    $params['utmgclid'] = $match[1];
                }
            }
        }
        return $this;
    }
    
    protected function addClientId(&$params){
        foreach($this->cookie->getIterator() as $key => $value){
            preg_match("/^_ga$/", $key, $match);
            if (is_array($match) && count($match) > 1 && $match[0]){
                $value = preg_match("/^GA[\d]{1,2}\.[\d]*\.(.*)$/", $value, $match);
                if (is_array($match)&& $match[1]){
                    $params['utmcmd'] = $match[1];
                }
            }
        }
        return $this;
    }
    
    protected function addUtmzParams(&$params){
        // utmgclid - unique id grom google adwords
        // utmccn - info about adwords or organic search
        // utmcmd - google client id
        // utmcsr - referal
        // utmcct - 
        // utmctr - keyword     
        $lookUp = array('utmccn', 'utmcmd', 'utmcsr', 'utmcct', 'utmctr');
        $list = explode("|", (string)$this->cookie->get("__utmz"));
        if (sizeof($list) > 0) {
            foreach ($list as $val) {
                for ($i = 0; $i < sizeof($lookUp); $i++) {
                    $pos = strpos($val, $lookUp[$i]);
                    if ($pos !== false) {
                        $params[$lookUp[$i]] = substr($val, $pos + strlen($lookUp[$i]) + 1);
                    }
                }
            }
        }
    }
    
    /**
     * Return Collected data by OrderId or CustomerId&&BasketId
     * @param int $order_id
     * @param int $customer_id
     * @param int $basket_id
     * @return boolean
     */
    public function getCollectedData(int $order_id = 0, int $customer_id = 0, int $basket_id = 0){
        if ($order_id){
            return $this->repository->getInstanceByOrderId($order_id);
        } elseif ($customer_id && $basket_id) {
            return $this->repository->getInstanceByBasketId($customer_id, $basket_id);
        }
        return false;
    }

    /**
     * Describe data (user_agent, resolution, ... from getCollectedData)
     * @param type $collectedData
     */
    public function describeCollection($collectedData){
        if (is_object($collectedData)){
            $obj = new \ArrayObject($collectedData->getAttributes(), \ArrayObject::ARRAY_AS_PROPS);
            $sz = unserialize($obj->user_agent);
            $obj->agent_name = @$sz['browser'] . ' ' . @$sz['version'];
            $obj->os_name = @$sz['platform'];
            $obj->origin = (tep_not_null($obj->utmgclid) && $obj->utmgclid == 'recoveryemail' && defined('BOX_TOOLS_RECOVER_CART') ? BOX_TOOLS_RECOVER_CART : $obj->utmcsr);
            $obj->java = tep_not_null($obj->resolution) ? (defined('TEXT_BTN_YES')?TEXT_BTN_YES:'Yes') : (defined('TEXT_BTN_NO')?TEXT_BTN_NO:'No');
            return $obj;
        }
        return false;
    }
}
