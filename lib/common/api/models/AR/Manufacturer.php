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

namespace common\api\models\AR;


use common\api\models\AR\Manufacturer\Info;
use yii\db\Expression;
use yii\helpers\FileHelper;

class Manufacturer extends EPMap
{

    protected $childCollections = [
        'infos' => [],//'\\common\\api\\models\\AR\\Manufacturer\\Info',
    ];

    public $manufacturers_image_data = '';
    public $manufacturers_image_source_url = '';
    public $manufacturers_image_after_save = false;

    public static function tableName()
    {
        return TABLE_MANUFACTURERS;
    }

    public static function primaryKey()
    {
        return ['manufacturers_id'];
    }

    public function customFields()
    {
        $fields = parent::customFields();
        $fields[] = 'manufacturers_image_data';
        $fields[] = 'manufacturers_image_source_url';
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInfos()
    {
        return $this->hasMany(Info::className(), ['manufacturers_id'=>'manufacturers_id']);
    }

// SeoRedirectsNamed moved to extensions/SeoRedirectsNamed/models/
//    public function getSeoRedirectsNamed()
//    {
//        return $this->hasMany(\common\models\SeoRedirectsNamed::className(), ['owner_id' => 'manufacturers_id'])->andWhere(['redirects_type'=>'brand']);
//    }


    public function getPossibleKeys()
    {
        $possibleKeys = parent::getPossibleKeys();
        $nestedCollectObject = new Info();
        $infoKeys = $nestedCollectObject->getPossibleKeys();
        foreach(Info::getAllKeyCodes() as $keyCode=>$lookupPK){
            foreach ($infoKeys as $infoKey) {
                $possibleKeys[] = 'infos.' . $keyCode . '.'.$infoKey;
            }
        }
        return $possibleKeys;
    }


    public function initCollectionByLookupKey_Infos($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Info::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['infos'][$keyCode] = null;
            if ( is_null($this->manufacturers_id) ) {
                $this->childCollections['infos'][$keyCode] = new Info($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['infos'][$keyCode])) {
                    $lookupPK['manufacturers_id'] = $this->manufacturers_id;
                    $this->childCollections['infos'][$keyCode] = Info::findOne($lookupPK);
                    if (!is_object($this->childCollections['infos'][$keyCode])) {
                        $this->childCollections['infos'][$keyCode] = new Info($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['infos'];
    }

    public function exportArray(array $fields = [])
    {

        if (!empty($this->manufacturers_image) && is_file(\common\classes\Images::getFSCatalogImagesPath() . $this->manufacturers_image)) {
            if (count($fields) == 0 || array_key_exists('manufacturers_image_data', $fields)) {
                //$this->manufacturers_image_data = file_get_contents(\common\classes\Images::getFSCatalogImagesPath().$this->manufacturers_image);
            }
            if (count($fields) == 0 || array_key_exists('manufacturers_image_source_url', $fields)) {
                $this->manufacturers_image_source_url = \Yii::$app->get('platform')->config()->getCatalogBaseUrl() . DIR_WS_IMAGES/*.\common\classes\Images::getWSCatalogImagesPath(false)*/ . rawurlencode($this->manufacturers_image);
            }
        }

        $data = parent::exportArray($fields);

        if ( (count($fields)==0 || array_key_exists('manufacturers_image_source_url', $fields)) && !empty($this->manufacturers_image_source_url) ) {
            $data['manufacturers_image_source_url'] = $this->manufacturers_image_source_url;
        }
        if ( (count($fields)==0 || array_key_exists('manufacturers_image_data', $fields)) && !empty($this->manufacturers_image_data) ) {
            $data['manufacturers_image_data'] = base64_encode($this->manufacturers_image_data);
        }

        return $data;
    }

    public function importArray($data)
    {
        $result = parent::importArray($data);

        if ( isset($data['manufacturers_image_data']) && !empty($data['manufacturers_image_data']) ) {
            $this->manufacturers_image_data = base64_decode($data['manufacturers_image_data']);
        }elseif ( array_key_exists('manufacturers_image_source_url',$data) && !empty($data['manufacturers_image_source_url']) ){
            $this->manufacturers_image_source_url = $data['manufacturers_image_source_url'];
        }

        return $result;
    }

    public function beforeSave($insert)
    {
        $targetDir = \common\classes\Images::getFSCatalogImagesPath();
        if ( !empty($this->manufacturers_image_source_url) || !empty($this->manufacturers_image_data) ) {
            $targetFilename = !empty($this->manufacturers_image)?$this->manufacturers_image:basename($this->manufacturers_image_source_url);
            if ( !empty($this->manufacturers_image_source_url) ) {
                if ( !is_dir(dirname($targetDir.$targetFilename)) ) {
                    try {
                        FileHelper::createDirectory(dirname($targetDir.$targetFilename), 0777);
                    }catch (\Exception $ex){}
                }
                @copy($this->manufacturers_image_source_url, $targetDir.$targetFilename);
            }elseif (!empty($this->manufacturers_image_data) && !empty($targetFilename)) {
                @file_put_contents($targetDir.$targetFilename, $this->manufacturers_image_data);
                unset($this->manufacturers_image_data);
            }
            if ( empty($this->manufacturers_image) ) {
                $this->manufacturers_image_after_save = [
                    $targetDir.$targetFilename,
                    $targetDir.'brands/%ID/gallery/'.$targetFilename,
                    'brands/%ID/gallery/'.$targetFilename,
                ];
            }
        }

        if ( $insert ) {
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression('NOW()');
            }
        }else {
            if ($this->isModified()) {
                $this->last_modified = new Expression('NOW()');
            }
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (is_array($this->manufacturers_image_after_save) && !empty($this->manufacturers_image_after_save)){
            $moveFrom = $this->manufacturers_image_after_save[0];
            $moveTo = str_replace('%ID', $this->manufacturers_id, $this->manufacturers_image_after_save[1]);
            $relName = str_replace('%ID', $this->manufacturers_id, $this->manufacturers_image_after_save[2]);
            if (@rename($moveFrom, $moveTo)){
                \common\classes\Images::createWebp($relName,true);
                \common\classes\Images::createResizeImages($relName, 'Brand gallery', true);
            }
            $this->manufacturers_image = $relName;
            $this->manufacturers_image_after_save = false;
            $this->save(false);
        }
    }


}