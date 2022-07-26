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
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "admin_platforms".
 *
 * @property integer $id
 * @property integer $service_id 
 * @property string $cloud_printer_id
 * @property string $cloud_printer_name
 * @property integer $status 
 */
class CloudPrinters extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cloud_printers';
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
            [['service_id', 'cloud_printer_id'], 'required'],
            [['service_id'], 'integer'],
            [['cloud_printer_id', 'cloud_printer_name'], 'string'],
            [['status'], 'default', 'value' => 0]
        ];
    }
    
    public function getService(){
        return $this->hasOne(CloudServices::class, ['id' => 'service_id']);
    }

    public function beforeDelete() {
        foreach(CloudPrintersDocuments::findAll(['printer_id' => $this->id]) as $document){
            $document->delete();
        }
        return parent::beforeDelete();
    }
    
    public function getDocuments(){
        return $this->hasMany(CloudPrintersDocuments::class, ['printer_id' => 'id']);
    }
}
