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
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "admin_platforms".
 *
 * @property integer $id
 * @property string $service 
 * @property integer $platform_id
 * @property string $key 
 */
class CloudServices extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cloud_services';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id'], 'required'],
            [['platform_id'], 'integer'],
            [['service', 'key'], 'string']
        ];
    }
    
    public function getPrinters(){
        return $this->hasMany(CloudPrinters::class, ['service_id' => 'id']);
    }
    
    public function beforeDelete() {
        foreach(CloudPrinters::findAll(['service_id' => $this->id]) as $printer){
            $printer->delete();
        }
        return parent::beforeDelete();
    }
    
    public function keyExists(){
        return !empty($this->key) && is_file(\common\helpers\Printers::getConfigPath() . $this->key);
    }

}
