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

class SuppliersCurrencies extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'suppliers_currencies';
    }
    
    public static function primaryKey() {
        return ['suppliers_id', 'currencies_id'];
    }
    
    public function getCurrencies(){
        return $this->hasOne(Currencies::className(), ['currencies_id' => 'currencies_id' ]);
    }


    public static function create($suppliers_id, $currencies_id){
        $sCurrency = self::findOne(['suppliers_id' => $suppliers_id, 'currencies_id' => $currencies_id]);
        if (!$sCurrency) {
            $sCurrency = new self();
            $sCurrency->suppliers_id = $suppliers_id;
            $sCurrency->currencies_id = $currencies_id;
        }
        return $sCurrency;
    }
    
    public function prepareData($data){
        if (!($this->suppliers_id || $this->currencies_id)){
            throw new \Exception('Currencies id and suppliers id are not defined');
        }
        
        $currency = Yii::$container->get('currencies');
        $rates = \yii\helpers\ArrayHelper::map($currency->currencies, 'id', 'value');
        $data['use_default'] = $data['use_default']??null;
        $this->setAttributes([            
            'status' => (int)($data['status'] ?? null),
            'use_custom_currency_value' => !(int)$data['use_default'],
            'currency_value' => !(int)$data['use_default'] ? (float)$data['custom_currency_value']: $rates[$this->currencies_id],
            'margin_value' => (float)($data['margin_value'] ?? null),
            'margin_type' => $data['margin_type'] ?? null
        ], false);
    }
}