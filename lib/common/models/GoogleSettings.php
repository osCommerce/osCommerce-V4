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

namespace common\models;
use Yii;
use yii\db\ActiveRecord;


class GoogleSettings extends ActiveRecord {
    
    public static function tableName() {
        return 'google_settings';
    }
    
    public static function primaryKey() {
        return ['google_settings_id'];
    }
    
    public function rules() {
        return [
            [['platform_id', 'status'], 'integer'],
            ['info', 'safe'],
            [['module_name', 'module'], 'string'],
        ];
    }

    public static function find() {
        return new queries\GoogleSettingsQuery(get_called_class());
    }
    
    public function getValue(){
        return $this->info;
    }
    
    public function setValue($value){
        $this->info = $value;
    }
}