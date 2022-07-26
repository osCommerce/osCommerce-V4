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
use common\api\models\AR\Products\Images\Description as ImageDescription;
use common\api\models\AR\Products\Images\ExternalUrl;
use common\models\ProductsImagesAttributes;

class Images extends EPMap
{
    protected $hideFields = [
        'products_images_id',
        'products_id',
    ];

    protected $childCollections = [
        'image_description' => [],
    ];

    protected $assign_to_attributes;

    protected $childExternalUrls;

    /**
     * @var EPMap
     */
    protected $parentObject;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->afterSaveHooks['Image::normalize'] = 'normalizeImageFiles';
    }


    public static function tableName()
    {
        return TABLE_PRODUCTS_IMAGES;
    }

    public static function primaryKey()
    {
        return ['products_images_id'];
    }

    public function refresh()
    {
        $this->childExternalUrls = false;
        return parent::refresh();
    }


    public function getAssocExternalUrls()
    {
        if ( !is_array($this->childExternalUrls) ) {
            $this->childExternalUrls = [];
            if ( $this->products_images_id ) {
                foreach (ExternalUrl::find()->where(['products_images_id' => $this->products_images_id])->orderBy(['image_types_id' => SORT_ASC])->all() as $obj) {
                    if ( $obj->language_id==0 ) {
                        $keyCode = '00';
                    }else {
                        $keyCode = \common\classes\language::get_code($obj->language_id, true);
                        if (!$keyCode) continue;
                    }
                    if ( !isset($this->childExternalUrls[$keyCode]) ) {
                        $this->childExternalUrls[$keyCode] = [];
                    }
                    $this->childExternalUrls[$keyCode][] = $obj;
                }
            }
        }
        return$this->childExternalUrls;
    }

    public function initCollectionByLookupKey_ImageDescription($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);

        if ( true ) {
            if (!is_null($this->products_images_id)) {
                $dbMapCollect = [];
                foreach (ImageDescription::findAll(['products_images_id' => $this->products_images_id]) as $obj) {
                    if ($obj->language_id == 0) {
                        $code = '00';
                    } else {
                        $code = \common\classes\language::get_code($obj->language_id, true);
                        if ($code == false) continue;
                    }
                    $dbMapCollect[$code] = $obj;
                }
                foreach (ImageDescription::getAllKeyCodes() as $keyCode => $lookupPK) {
                    if ($loadAll || in_array($keyCode, $lookupKeys)) {
                        if (isset($dbMapCollect[$keyCode])) {
                            $this->childCollections['image_description'][$keyCode] = $dbMapCollect[$keyCode];
                        } else {
                            $lookupPK['products_images_id'] = (int)$this->products_images_id;
                            $this->childCollections['image_description'][$keyCode] = new ImageDescription($lookupPK);
                        }
                        $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
                    }
                }
            } else {
                foreach (ImageDescription::getAllKeyCodes() as $keyCode => $lookupPK) {
                    $this->childCollections['image_description'][$keyCode] = new ImageDescription($lookupPK);
                    $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
                }
            }
        }else {
            foreach (ImageDescription::getAllKeyCodes() as $keyCode => $lookupPK) {
                if (is_object($this->childCollections['image_description'][$keyCode])) continue;
                $this->childCollections['image_description'][$keyCode] = null;
                if (is_null($this->products_images_id)) {
                    $this->childCollections['image_description'][$keyCode] = new ImageDescription($lookupPK);
                    $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
                } elseif ($loadAll || in_array($keyCode, $lookupKeys)) {
                    if (!is_object($this->childCollections['image_description'][$keyCode])) {
                        $lookupPK['products_images_id'] = $this->products_images_id;
                        $this->childCollections['image_description'][$keyCode] = ImageDescription::findOne($lookupPK);
                        if (!is_object($this->childCollections['image_description'][$keyCode])) {
                            $this->childCollections['image_description'][$keyCode] = new ImageDescription($lookupPK);
                        }
                        $this->childCollections['image_description'][$keyCode]->parentEPMap($this);
                    }
                }
            }
        }
        return $this->childCollections['image_description'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->parentObject = $parentObject;
    }

    public function getImageHashes()
    {
        if ( count($this->childCollections['image_description'])==0 ) {
            $this->initCollectionByLookupKey_ImageDescription(['*']);
        }
        $hashes = [];
        foreach($this->childCollections['image_description'] as $key=>$imageDesc){
            $hashes[$key] = $imageDesc->hash_file_name;
        }
        return $hashes;
    }
    public function getImageCompareKeys()
    {
        if ( count($this->childCollections['image_description'])==0 ) {
            $this->initCollectionByLookupKey_ImageDescription(['*']);
        }
        $hashes = [];
        foreach($this->childCollections['image_description'] as $key=>$imageDesc){
            $hashes[$key] = [
                'products_images_id' => $imageDesc->products_images_id,
                'language_id' => $imageDesc->language_id,
                'hash' => $imageDesc->hash_file_name,
                'orig_name' => $imageDesc->orig_file_name,
            ];
        }
        return $hashes;
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( isset($importedObject->products_images_id) && intval($importedObject->products_images_id)>0 ){
            if (intval($importedObject->products_images_id)==intval($this->products_images_id)) {
                $this->pendingRemoval = false;
                return true;
            }
            return false;
        }

        $k1_match = 0;
        $k2_match = 0;
        $this_keys = $this->getImageCompareKeys();
        $imported_keys = $importedObject->getImageCompareKeys();
        foreach ( $this_keys as $key=>$compareValues ) {
            if ( !empty($compareValues['hash']) && isset($imported_keys[$key]['hash']) && $compareValues['hash']==$imported_keys[$key]['hash'] ) {
                $k1_match++;
            }
            if ( !empty($compareValues['orig_name']) && isset($imported_keys[$key]['orig_name']) && $compareValues['orig_name']==$imported_keys[$key]['orig_name'] ) {
                $k2_match++;
            }
        }
        if ( $k1_match>0 || $k2_match>0 ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;

        /*
        $match_images = 0;
        $this_hashes = $this->getImageHashes();
        $imported_hashes = $importedObject->getImageHashes();
        foreach ( $this_hashes as $key=>$hash ) {
            if ( !empty($hash) && isset($imported_hashes[$key]) && $hash==$imported_hashes[$key] ) {
                $match_images++;
            }
        }
        if ( $match_images>0 ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;
        */
    }

    public function importArray($data)
    {
        //if ( count($this->childCollections['image_description'])==0 ) {
        //    $this->initCollectionByLookupKey_ImageDescription([]);
        //}
        if ( isset($data['products_images_id']) && (int)$data['products_images_id']>0 ) $data['products_images_id'] = (int)$data['products_images_id'];
        $result = parent::importArray($data);
        if ( isset($data['assign_to_attributes']) && is_array($data['assign_to_attributes']) ) {
            $this->assign_to_attributes = $data['assign_to_attributes'];
        }
        return $result;
    }

    public function beforeDelete()
    {
        if ( count($this->childCollections['image_description'])==0 ) {
            $this->initCollectionByLookupKey_ImageDescription(['*']);
        }
        foreach( $this->childCollections['image_description'] as $imageDescription ) {
            $imageDescription->delete();
        }

        \common\classes\Images::removeProductImage($this->products_id, $this->products_images_id);

        return parent::beforeDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( is_array($this->assign_to_attributes) && count($this->assign_to_attributes)>0 ) {
            foreach ($this->assign_to_attributes as $optionId=>$valuesIds){
                if ( !is_array($valuesIds) ) continue;
                $valuesIds = array_flip($valuesIds);
                foreach (ProductsImagesAttributes::find()
                    ->where(['AND',['products_images_id'=>$this->products_images_id],['products_options_id'=>$optionId]])
                    ->all() as $existingAssign){
                    if ( !isset($valuesIds[$existingAssign->products_options_values_id]) ) {
                        $existingAssign->delete();
                    }else{
                        unset($valuesIds[$existingAssign->products_options_values_id]);
                    }
                }
                foreach ($valuesIds as $missingValueId) {
                    $missingMap = new ProductsImagesAttributes([
                        'products_images_id' => $this->products_images_id,
                        'products_options_id' => $optionId,
                        'products_options_values_id' => $missingValueId,
                    ]);
                    $missingMap->loadDefaultValues();
                    $missingMap->save(false);
                }
            }
        }
        // assign_to_attributes
    }

    protected function normalizeImageFiles()
    {
        \common\classes\Images::normalizeImageFiles($this->products_id, $this->products_images_id);
    }
}