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
use yii\helpers\Inflector;
use common\models\repositories\GoogleSettingsRepository;

class CaptchaProvider extends Providers implements GoogleProviderInterface {

    private $gsRepository;

    private $code = 'recaptcha';
    
    public function getName(){
        return 'reCaptcha Keys';
    }
    
    public function getCode(){
        return $this->code;
    }

    public function getDescription(){
        return 'That pair of keys is used to provide google reCaptcha verification. They can be obtained at <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">Google reCAPTCHA Console</a>';
    }
    
    public function __construct(GoogleSettingsRepository $gsRepository){
        $this->gsRepository = $gsRepository;
    }
    
    public function getConfig($platformId = 0){
        static $setting = null;
        if (is_null($setting) || ($setting == false) || ($setting && $platformId != $setting->platform_id)){
            $setting = $this->getSetting($platformId);
        }
        if ($setting){
            $value = $setting->getValue();
            return $value ? $value : false;
        } else {
            $setting = false;
        }        
        return false;
    }
    
    public function getSetting($platformId = 0){
        return $this->gsRepository->getSetting($this->code, $platformId, 1);
    }
    
    private function prepareConfig($data){
        if (is_array($data) && isset($data['publicKey']) && isset($data['privateKey'])){
            try{
                return \GuzzleHttp\json_encode([
                    'publicKey' => (string)$data['publicKey'],
                    'privateKey' => (string)$data['privateKey'],
                    'version' => (string)$data['version'],
                ]);
            } catch (\Exception $ex) {
            }
        }
        return false;
    }
    
    public function updateSetting($setting, $data, $platformId){
        if ($config = $this->prepareConfig($data)){
            return $this->gsRepository->updateSetting($setting, [ $this->gsRepository->getConfigHolder() => $config, 'platform_id' => $platformId ]);
        }
        return false;
    }
    
    public function createSetting($data, $platformId){
        if ($config = $this->prepareConfig($data)){
            return $this->gsRepository->createSetting($this->getCode(), $this->getName(), $config, $platformId, 1);
        }
        return false;
    }
    
    private function _decode($config){
        try{
            return \GuzzleHttp\json_decode($config, true);
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function getPublicKey($platformId = 0){
        $config = $this->getConfig($platformId);
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['publicKey'];
            }
        }
        return false;
    }
    
    public function getPrivateKey($platformId = 0){
        $config = $this->getConfig($platformId);
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['privateKey'];
            }
        }
        return false;
    }
    
    public function getVersion($platformId = 0){
        $config = $this->getConfig($platformId);
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['version'] ?? 'v2';
            }
        }
        return false;
    }
    
    public function drawConfigTemplate($platformId = 0){
        return widgets\CaptchaWidget::widget(['publicKey' => $this->getPublicKey($platformId), 'privateKey' => $this->getPrivateKey($platformId), 'version' => $this->getVersion($platformId), 'owner' => $this->getClassName(), 'description' => $this->getDescription()]);
    }
}
