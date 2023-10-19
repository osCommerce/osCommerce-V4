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
use common\models\repositories\GoogleSettingsRepository;

class AnalyticsProvider extends Providers implements GoogleProviderInterface{

    private $gsRepository;

    private $code = 'report';
    
    public $hasConfigFile = true;
    
    private $uploadPath;
    private $configPath;
        
    public function getName(){
        return 'Google Analytics Keys';
    }
    
    public function getCode(){
        return $this->code;
    }

    public function getDescription(){
        return 'Analytics View Id & its accompanied file with credentials are used to measurement statistical information. <a href="https://developers.google.com/analytics/devguides/collection/protocol/v1/" target="_blank">More details</a>';
    }
    
    public function __construct(GoogleSettingsRepository $gsRepository){
        $this->gsRepository = $gsRepository;
        if (\frontend\design\Info::isTotallyAdmin()){
            $this->uploadPath = Yii::$aliases['@webroot'] . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;
        }
        $this->configPath = Yii::$aliases['@common'] . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "google" . DIRECTORY_SEPARATOR;
        if (!is_dir($this->configPath)){
            \yii\helpers\FileHelper::createDirectory($this->configPath, 0755);
        }
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
        return $this->gsRepository->getSetting($this->code, $platformId, null);
    }
    
    protected function prepareConfig($data){
        if (is_array($data) && (isset($data['jsonFile']) || isset($data['viewId']))){
            $jsonFile = (string)$data['jsonFile'];
            if (is_file($this->uploadPath . $jsonFile)){
                try{
                    if (copy($this->uploadPath . $jsonFile, $this->configPath . $jsonFile)){
                        unlink($this->uploadPath . $jsonFile);
                    }
                } catch (\Exception $ex) {
                    
                }
            }
            return \GuzzleHttp\json_encode([
                'jsonFile' => $jsonFile,
                'viewId' => (string)$data['viewId'],
            ]);
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
    
    public function getFileKey($platformId = 0){
        $config = $this->getConfig($platformId);
        if ($config){
            $values = $this->_decode($config);
            if ($values && is_file($this->configPath . $values['jsonFile'])){
                return $this->configPath . $values['jsonFile'];
            }
        }
        return false;
    }
    
    public function getViewId($platformId = 0){
        $config = $this->getConfig($platformId);
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['viewId'];
            }
        }
        return false;
    }
    
    public function drawConfigTemplate($platformId = 0){
        return widgets\AnalyticsWidget::widget(['jsonFile' => $this->getFileKey($platformId), 'viewId' => $this->getViewId($platformId), 'owner' => $this->getClassName(), 'description' => $this->getDescription(), 'platformId' => $platformId]);
    }
}
