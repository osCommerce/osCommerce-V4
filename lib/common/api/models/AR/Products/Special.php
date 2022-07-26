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

namespace common\api\models\AR\Products;


use common\api\models\AR\EPMap;
use yii\db\Expression;

class Special extends EPMap
{

    protected $parentObject;

    protected $childCollections = [
        'prices' => [],
    ];

    public static function tableName()
    {
        return 'specials';
    }

    public static function primaryKey()
    {
        return ['specials_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->parentObject = $parentObject;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $this->pendingRemoval = false;
        if ( !empty($importedObject->specials_id) && intval($importedObject->specials_id)==intval($this->specials_id) ){
            return true;
        }
        if (
            (!is_null($this->start_date) && $importedObject->start_date===$this->start_date)
            &&
            (!is_null($this->expires_date) && $importedObject->expires_date===$this->expires_date)
            /*&&
            ($importedObject->status===$this->status)*/
        ){
            return true;
        }
        return false;
    }

    public function importArray($data)
    {
        if ( $this->isNewRecord ) {
            $data['status'] = isset($data['status'])?($data['status']?1:0):1;
            $data['start_date'] = isset($data['start_date'])?strval($data['start_date']):null;
            $data['expires_date'] = isset($data['expires_date'])?strval($data['expires_date']):null;
        }
        if ( !is_array($this->childCollections['prices']) || count($this->childCollections['prices'])==0 ){
            $this->initCollectionByLookupKey_Prices(['*']);
        }

        if ( array_key_exists('specials_new_products_price', $data ) ){
            $defPriceKey = \common\helpers\Currencies::systemCurrencyCode().'_0';
            if ( isset($this->childCollections['prices'][$defPriceKey]) && !isset($data['prices'][$defPriceKey]['specials_new_products_price']) ) {
                $data['prices'][$defPriceKey]['specials_new_products_price'] = $data['specials_new_products_price'];
            }
        }

        return parent::importArray($data);
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*', $lookupKeys);
        if (true) {
            if ( !is_null($this->specials_id) ) {
                $dbMapCollect = [];
                foreach(SpecialPrices::findAll(['specials_id' => $this->specials_id]) as $obj){
                    $keyCode = $obj->currencies_id.'_'.$obj->groups_id;
                    $dbMapCollect[$keyCode] = $obj;
                }
                foreach(SpecialPrices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    if( $loadAll || in_array($keyCode,$lookupKeys) ) {
                        $dbKeyCode = $lookupPK['currencies_id'].'_'.$lookupPK['groups_id'];
                        if (isset($dbMapCollect[$dbKeyCode])) {
                            $this->childCollections['prices'][$keyCode] = $dbMapCollect[$dbKeyCode];
                        } else {
                            $lookupPK['specials_id'] = $this->specials_id;
                            $this->childCollections['prices'][$keyCode] = new SpecialPrices($lookupPK);
                        }
                    }
                }
                unset($dbMapCollect);
            }else{
                foreach(SpecialPrices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    $this->childCollections['prices'][$keyCode] = new SpecialPrices($lookupPK);
                }
            }
        }else{
            foreach (SpecialPrices::getAllKeyCodes() as $keyCode => $lookupPK) {
                $this->childCollections['prices'][$keyCode] = null;
                if (is_null($this->specials_id)) {
                    $this->childCollections['prices'][$keyCode] = new SpecialPrices($lookupPK);
                } elseif ($loadAll || in_array($keyCode, $lookupKeys)) {
                    if (!isset($this->childCollections['prices'][$keyCode])) {
                        $lookupPK['specials_id'] = $this->specials_id;
                        $this->childCollections['prices'][$keyCode] = SpecialPrices::findOne($lookupPK);
                        if (!is_object($this->childCollections['prices'][$keyCode])) {
                            $this->childCollections['prices'][$keyCode] = new SpecialPrices($lookupPK);
                        }
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }

    public function beforeSave($insert)
    {
        if ( $insert && empty($this->specials_date_added)){
            $this->specials_date_added = new \yii\db\Expression('NOW()');
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ( $insert && defined('SALE_STRICT_DATE') && SALE_STRICT_DATE=='True' ) {
            if ( !is_null($this->start_date) || !is_null($this->expires_date) ){
                $this->refresh();

                // ------|==|-----------// --------------------- 1
                // -------|=====|-------// -----------|=|------- 2
                // ---|=====|-----------// ---|=|--------------- 3
                // =========|-----------// =====|--------------- 4
                // ---------|===========// -----------|========= 5
                // --|===========|------// --|==|-----|==|------ 6 => 3
                //+-----|+++++|---------//+-----|+++++|---------
                $checkCollection = static::find()
                    ->where(['AND',['products_id'=>$this->products_id],['!=', 'specials_id', $this->specials_id]])
                    ->andWhere(['OR',['status'=>1],['>=','start_date',new Expression('NOW()')]])
                    ->all();
                foreach ($checkCollection as $checkModel){
                    $dbSpecial = [ 's' => strtotime($checkModel->start_date), 'e' => strtotime($checkModel->expires_date) ];
                    $newSpecial = [ 's' => strtotime($this->start_date), 'e' => strtotime($this->expires_date) ];
//echo '<pre>NEW '; var_dump(date(DATE_ATOM, $newSpecial['s']), date(DATE_ATOM, $newSpecial['e'])); echo '</pre>';
//echo '<pre>DB '; var_dump(date(DATE_ATOM, $dbSpecial['s']), date(DATE_ATOM, $dbSpecial['e'])); echo '</pre>';
                    if ( intval($newSpecial['s'])>0 && intval($newSpecial['e'])>0 ){
                        if ( intval($dbSpecial['s'])>0 && intval($dbSpecial['e'])>0 ){
                            //123
                            if ( $dbSpecial['s']>=$newSpecial['s'] && $dbSpecial['e']<=$newSpecial['e'] ){ //1
                                $checkModel->start_date = null;
                                $checkModel->expires_date = null;
                                if ($this->status){
                                    $checkModel->status = 0;
                                }
                            }else {
                                if ($dbSpecial['s'] > $newSpecial['s'] && $dbSpecial['e'] > $newSpecial['e']) { //2
                                    $checkModel->start_date = date('Y-m-d H:i:s', $newSpecial['e'] + 1);
                                } else if ($dbSpecial['s'] < $newSpecial['s'] && $newSpecial['s']<$dbSpecial['e'] && $dbSpecial['e'] < $newSpecial['e']) { //3
                                    $checkModel->expires_date = date('Y-m-d H:i:s', $newSpecial['s'] - 1);
                                }
                            }
                        }elseif ( intval($dbSpecial['s'])>0 && intval($dbSpecial['e'])==0 ){ //5
                            if ( $newSpecial['e']>$dbSpecial['s'] ){
                                $checkModel->start_date = date('Y-m-d H:i:s', $newSpecial['e']+1);
                            }
                        }elseif ( intval($dbSpecial['s'])==0 && intval($dbSpecial['e'])>0 ){ //4
                            if ( $dbSpecial['e']>$newSpecial['s'] ){
                                $checkModel->expires_date = date('Y-m-d H:i:s', $newSpecial['s']-1);
                            }
                        }elseif ( intval($dbSpecial['s'])==0 && intval($dbSpecial['e'])==0 ){

                        }
                    }elseif ( intval($newSpecial['s'])>0 && intval($newSpecial['e'])==0 ){
                        // ------|==|-----------// --------------------- 1
                        // -------|=====|-------// --------------------- 2
                        // ---|=====|-----------// ---|=|--------------- 3
                        // =========|-----------// =====|--------------- 4
                        // ---------|===========// --------------------- 5
                        //+-----|+++++++++++++++//+-----|+++++++++++++++
                        if ( intval($dbSpecial['s'])>0 ) { //(1 2 3 5)
                            if ( intval($dbSpecial['s'])<intval($newSpecial['s']) ) { //3
                                if ( intval($dbSpecial['e'])>intval($newSpecial['e']) ) {
                                    $checkModel->expires_date = date('Y-m-d H:i:s', $newSpecial['s']-1);
                                }
                            }else{ //1 2 5
                                $checkModel->start_date = null;
                                $checkModel->expires_date = null;
                            }
                        }elseif ( intval($dbSpecial['e'])>0 ){ // (4)
                            if ( intval($dbSpecial['e'])>intval($newSpecial['s']) ) { //4
                                $checkModel->expires_date = date('Y-m-d H:i:s', $newSpecial['s']-1);
                            }
                        }else{
                            // status
                        }

                    }elseif ( intval($newSpecial['s'])==0 && intval($newSpecial['e'])>0 ){
                        //expire
                        // ------|==|-----------// ------|==|----------- 1
                        // -------|=====|-------// -------|=====|------- 2
                        // ---|=====|-----------// -----|===|----------- 3
                        // =========|-----------// -----|===|----------- 4
                        // ---------|===========// ---------|=========== 5
                        //++++++|---------------//++++++|---------------
                        if ( intval($dbSpecial['s'])>0 && intval($dbSpecial['s'])<intval($newSpecial['e']) ){ //3
                            $checkModel->start_date = date('Y-m-d H:i:s', $newSpecial['e']+1);
                        }
                        if ( intval($dbSpecial['e'])>0 && intval($dbSpecial['e'])<intval($newSpecial['e']) ) { //4
                            $checkModel->expires_date = date('Y-m-d H:i:s', $newSpecial['e']+1);
                        }
                    }
                    $checkModel->save();
                }
                $this->parentObject->initiateAfterSave('Product::SpecialClean');
            }

//            die;
            /*
            $this->refresh();
            if ( $this->start_date ) {}
            if ( $this->expires_date ) {}
            */
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /*public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        foreach (SpecialPrices::getAllKeyCodes() as $pk){
            if ( !$insert && empty($pk['groups_id']) ) continue;

            $pk['specials_id'] = $this->specials_id;
            $priceModel = SpecialPrices::findOne($pk);
            if ( !$priceModel ) $priceModel = new SpecialPrices($pk);
            $priceModel->specials_new_products_price = empty($pk['groups_id'])?$this->specials_new_products_price:-2;
            $priceModel->save(false);
        }
    }*/

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        SpecialPrices::deleteAll(['specials_id'=>$this->specials_id]);

        return true;
    }


}