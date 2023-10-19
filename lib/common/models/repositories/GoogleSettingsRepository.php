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

namespace common\models\repositories;

use common\models\GoogleSettings;

class GoogleSettingsRepository {

    public function getConfigHolder(){
        return 'info';
    }
    
    protected function get(){
        return GoogleSettings::find();
    }
    
    public function findById($id){
        return $this->get()->where(['google_settings_id' => (int)$id])->one();
    }
    
    public function getSetting($module, $platform_id = 0, $status = null)
    {
        //$query = $this->get()->where(['module' => $module])->platform($platform_id)->status($status);
        $query = $this->get()->where(['module' => $module])
                ->andWhere(['platform_id' => $platform_id]);
        if (!is_null($status)) {
                $query->andWhere(['status' => $status]);
        }
        return $query->one();
    }
    
    public function getSettings(array $modules = [], $platform_id = 0, $status = null) :array
    {
        $query = $this->get()->where('1')
                ->modules($modules)
                ->status($status)
                ->platform($platform_id);
        return $query->all();
    }
    
    public function createSetting(string $moduleClass, string $moduleDesc, string $config, int $platformId =0, int $status = 0) :GoogleSettings
    {
        $setting =  new GoogleSettings([
            'module' => $moduleClass,
            'module_name' => $moduleDesc,
            'status' => $status,
            'info' => $config,
            'platform_id' => $platformId
        ]);
        $setting->validate() && $setting->save();
        return $setting;
    }

    public function updateSetting(GoogleSettings $setting, array $values){
        foreach($values as $key => $value){
            if ($setting->hasAttribute($key)){
                $setting->setAttribute($key, $value);
            }
        }
        return $setting->validate() && $setting->save();
    }
    
    public function delete($id){
        if ($object = $this->findById($id)){
            return $object->delete();
        }
        return false;
    }
    
}