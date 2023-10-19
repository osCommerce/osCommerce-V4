<?php
/**
 * Get access to modules, captcha, map, printers, analytics and their settings
 */

namespace common\components;

use Yii;

#[\AllowDynamicProperties]
class GoogleTools 
{
    
    private static $instance = null;
    public static function instance(){
        if (!is_object(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private $providers = [
        'ModulesProvider' => false,
        'MapProvider' => false,
        'PrinterProvider' => false,
        'CaptchaProvider' => false,
        'AnalyticsProvider' => false,
    ];
    
    public function getProvider($name){
        if (isset($this->providers[$name])){
            $getProvier = 'get' . ucfirst($name);
            if (method_exists($this, $getProvier)){
                return $this->{$getProvier}();
            }
        }
        return false;
    }
    
    public function updateProviderConfig(google\GoogleProviderInterface $provider, $config, $platformId = 0){
        $setting = $provider->getSetting($platformId);
        if ($setting){
            return $provider->updateSetting($setting, $config, $platformId);
        } else {
            return $provider->createSetting($config, $platformId);
        }

    }

    public function getModulesProvider(){
        if (!is_object($this->provider['ModulesProvider'] ?? null)) {
            $this->provider['ModulesProvider'] = Yii::createObject(__NAMESPACE__ . '\google\ModuleProvider');
        }
        return $this->provider['ModulesProvider'];
    }
    
    public function getMapProvider(){
        if (!is_object($this->provider['MapProvider'] ?? null)) {
            $this->provider['MapProvider'] = Yii::createObject(__NAMESPACE__ . '\google\MapProvider');
        }
        return $this->provider['MapProvider'];
    }
    
    public function getCaptchaProvider(){
        if (!is_object($this->provider['CaptchaProvider'] ?? null)) {
            $this->provider['CaptchaProvider'] = Yii::createObject(__NAMESPACE__ . '\google\CaptchaProvider');
        }
        return $this->provider['CaptchaProvider'];
    }
    
    public function getAnalyticsProvider(){
        if (!is_object($this->provider['AnalyticsProvider'] ?? null)) {
            $this->provider['AnalyticsProvider'] = Yii::createObject(__NAMESPACE__ . '\google\AnalyticsProvider');
        }
        return $this->provider['AnalyticsProvider'];
    }
    
    public function getGeocodingLocation(string $address){
        if (empty($address)) {
            return false;
        }
        return $this->getMapProvider()->getLocationByAddress($address);
    }
    
    public function checkOrderPosition(\common\classes\extended\OrderAbstract $order) {
        if ($order->order_id) return false;
        $nostreetaddress = implode(" ", [$order->customer['postcode'] ?? '', $order->customer['city'] ?? '', $order->customer['country']['title'] ?? '']);
        $address = implode(" ", [$order->customer['postcode'] ?? '', $order->customer['street_address'] ?? '', $order->customer['city'], $order->customer['country']['title'] ?? '']);
        $addressnocode = implode(" ", [$order->customer['street_address'] ?? '', $order->customer['city'] ?? '', $order->customer['country']['title'] ?? '']);
        foreach([$address, $addressnocode, $nostreetaddress] as $addr){
            if ($resp = $this->getMapProvider()->getLocationByAddress($addr)){
                $oModel = $order->getARModel()->where(['orders_id' => $order->order_id]);
                if ($oModel){
                    $oModel->lat = $resp['lat'];
                    $oModel->lng = $resp['lng'];
                    return $oModel->save(false);
                }
            }
        }
        return false;
    }
}
