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

class MapProvider extends Providers implements GoogleProviderInterface{

    private $gsRepository;

    private $code = 'mapskey';
    
    public function getName(){
        return 'Map Key API';
    }
    
    public function getCode(){
        return $this->code;
    }

    public function getDescription(){
        return 'This key is used to make requests with right access to Google Libraries like Maps JavaScript API. It can be obtained at <a href="https://console.developers.google.com/apis/credentials" target="_blank">Google Console</a>';
    }

    public function __construct(GoogleSettingsRepository $gsRepository){
        $this->gsRepository = $gsRepository;
    }
    
    public function getSetting(){
        return $this->gsRepository->getSetting($this->code, 0, 0);
    }
        
    public function updateSetting($setting, $data){
        if (is_array($data) && isset($data['key'])){
            $key = $data['key'];
            return $this->gsRepository->updateSetting($setting, [ $this->gsRepository->getConfigHolder() => (string)$key ]);
        }
        return false;
    }
    
    public function createSetting($data){
        if (is_array($data) && isset($data['key'])){
            return $this->gsRepository->createSetting($this->getCode(), $this->getName(), $data['key'], 0, 0);
        }
        return false;
    }
    
    public function getConfig(){
        $setting = $this->getSetting();
        if ($setting){
            $value = $setting->getValue();
            return $value ? $value : false;
        }
        return false;
    }
    
    public function getMapsKey(){
        return $this->getConfig();
    }
    
    public function drawConfigTemplate(){
        return widgets\MapWidget::widget(['value' => $this->getConfig(), 'owner' => $this->getClassName(), 'description' => $this->getDescription() ]);
    }
    
    public function getLocationByAddress(string $address){
        if ($key = $this->getMapsKey()){
            $query = http_build_query([
                'address' => $address,
                'key' => $key,
            ]);
            $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/']);
            try{
                $response = $client->get('maps/api/geocode/json?'.$query);
                if ($response){
                    $content = json_decode($response->getBody()->getContents());
                    if (is_object($response) && !empty($response->results) && $response->status == 'OK') {
                        $response = $response->results[0];
                        if (is_object($response) && property_exists($response, 'geometry')) {
                            $detail = $response->geometry;
                            if (property_exists($detail, 'location')) {
                                $detail = $detail->location;
                                if (property_exists($detail, 'lat') && property_exists($detail, 'lng')) {
                                    return [
                                        'lat' => (float) $detail->lat,
                                        'lng' => (float) $detail->lng,
                                    ];
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $ex) {
                return false;
            }
        }
        return false;
    }
}
