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

namespace common\classes;

use Yii;

class ReCaptcha {

    private $public_key;
    private $secret_key;
    private $version;
    private $url = 'https://www.google.com/recaptcha/api/siteverify';
    private $enabled;

    public function __construct() {
        $this->enabled = true;
        
        $provider = \common\components\GoogleTools::instance()->getCaptchaProvider();
        $platformId = \common\classes\platform::activeId();
        
        $this->public_key = $provider->getPublickey($platformId);
        $this->secret_key = $provider->getPrivateKey($platformId);
        $this->version = $provider->getVersion($platformId);
        
        if ($platformId > 0) {
            if (
                    (empty($this->public_key) || $this->public_key === false) ||
                    (empty($this->secret_key) || $this->secret_key === false)
                ) {
                $this->public_key = $provider->getPublickey(0);
                $this->secret_key = $provider->getPrivateKey(0);
                $this->version = $provider->getVersion(0);
            }
        }
        
        if (empty($this->public_key) || empty($this->secret_key)) {
            $this->enabled = false;
        }
    }
    
    public function isEnabled(){
        return $this->enabled;
    }
    
    public function getPublicKey(){
        return $this->public_key;
    }
    
    public function getVersion(){
        return $this->version;
    }

    public function checkVerification($user_value) {
        if (empty($user_value) || !$this->enabled)
            return false;
        $ch = curl_init($this->url);
        if ($ch) {
            $data = array('secret' => $this->secret_key, 'response' => $user_value, 'remoteip' => \common\helpers\System::get_ip_address());
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            if ($result === false) {
                return false;
            }
            curl_close($ch);
            $result = json_decode($result);
            return $result->success;
        }
        return false;
    }

}
