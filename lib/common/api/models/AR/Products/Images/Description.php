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

namespace common\api\models\AR\Products\Images;

use common\api\models\AR\EPMap;
use common\classes\Images;
use common\classes\language;
use yii\helpers\FileHelper;

class Description extends EPMap
{

    protected $hideFields = [
        'products_images_id',
        'language_id',
    ];

    protected $childCollections = [
        'external_urls' => false,
    ];

    protected $indexedCollections = [
        'external_urls' => 'common\api\models\AR\Products\Images\ExternalUrl',
    ];

    public $image_data = '';
    public $image_source_url = '';
    public $image_sources = [];

    protected $parentObject;
    private $generateImageThumbnails = false;

    public function __construct(array $config = [])
    {
        if ( !defined('TABLE_PRODUCTS_IMAGES_EXTERNAL_URL') ) {
            unset($this->childCollections['external_urls']);
            unset($this->indexedCollections['external_urls']);
        }
        parent::__construct($config);
    }


    public static function getAllKeyCodes()
    {
        $keyCodes = ['00'=>[
            'products_images_id' => null,
            'language_id' => 0,
        ]];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'];
            $keyCodes[$keyCode] = [
                'products_images_id' => null,
                'language_id' => $lang['id'],
            ];
        }
        return $keyCodes;
    }

    public static function tableName()
    {
        return TABLE_PRODUCTS_IMAGES_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['products_images_id', 'language_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        if ( (int)$parentObject->products_images_id>0 ) {
            $this->products_images_id = (int)$parentObject->products_images_id;
        }
        $this->parentObject = $parentObject;
    }

    public function customFields()
    {
        $fields = parent::customFields();
        $fields[] = 'image_data';
        $fields[] = 'image_source_url';
        return $fields;
    }

    public function initCollectionByLookupKey_ExternalUrls($lookupKeys)
    {
        if ( !is_array($this->childCollections['external_urls']) ) {
            $this->childCollections['external_urls'] = [];
            if ($this->products_images_id /*&& $this->use_external_images*/) {
                if ( $this->parentObject && method_exists($this->parentObject,'getAssocExternalUrls') ) {
                    $urls = $this->parentObject->getAssocExternalUrls();
                    if ( $this->language_id==0 ) {
                        $keyCode = '00';
                    }else {
                        $keyCode = \common\classes\language::get_code($this->language_id, true);
                    }
                    $this->childCollections['external_urls'] = isset($urls[$keyCode])?$urls[$keyCode]:[];
                }else {
                    $this->childCollections['external_urls'] =
                        ExternalUrl::find()
                            ->where(['products_images_id' => $this->products_images_id, 'language_id' => $this->language_id])
                            ->orderBy(['image_types_id' => SORT_ASC])
                            ->all();
                }
            }
        }
        return $this->childCollections['external_urls'];
    }

    public function exportArray(array $fields = [])
    {
        if ( count($fields)==0 || array_key_exists('image_data', $fields) ) {
            if ( !empty($this->hash_file_name) ) {
                $product_image_location = Images::getFSCatalogImagesPath().'products/'.$this->parentObject->products_id.'/'.$this->products_images_id.'/';
                $imageFilename = $product_image_location.$this->hash_file_name;
                if ( is_file($imageFilename) ){
                    //$this->image_data = file_get_contents($imageFilename);
                    $this->image_source_url = \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true)./*Images::getWSCatalogImagesPath(false).*/DIR_WS_IMAGES.'products/'.$this->parentObject->products_id.'/'.$this->products_images_id.'/'.$this->hash_file_name;
                }
                foreach(Images::getImageTypes() as $image_types){
                    $image_location = $product_image_location . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . DIRECTORY_SEPARATOR;
                    if ( $this->language_id ) {
                        $image_location .= language::get_code($this->language_id).DIRECTORY_SEPARATOR;
                    }
                    $imageName = $this->file_name;
                    if (!empty($this->alt_file_name)) {
                        $imageName = $this->alt_file_name;
                    }
                    if (file_exists($image_location . $imageName)) {
                        $partial_file_name = str_replace(Images::getFSCatalogImagesPath(),'',$image_location . $imageName);
                        $this->image_sources[] = [
                            'size' => $image_types['image_types_name'],
                            'url' => \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true)./*Images::getWSCatalogImagesPath(false).*/DIR_WS_IMAGES.$partial_file_name,
                        ];
                    }
                }
            }
        }
        $data = parent::exportArray($fields);
        if ( (count($fields)==0 || array_key_exists('image_data', $fields)) && !empty($this->image_data) ) {
            $data['image_data'] = base64_encode($this->image_data);
        }
        if ( (count($fields)==0 || array_key_exists('image_source_url', $fields)) && !is_null($this->image_source_url) ) {
            $data['image_source_url'] = $this->image_source_url;
            $data['image_sources'] = $this->image_sources;
        }

        return $data;
    }


    public function importArray($data)
    {
        $result = parent::importArray($data);

        if ( isset($data['image_data']) && !empty($data['image_data']) ) {
            $this->image_data = base64_decode($data['image_data']);
        }elseif ( array_key_exists('image_source_url',$data) && !empty($data['image_source_url']) ){
            if (
                isset($this->parentObject) && is_object($this->parentObject) && $this->products_images_id && $this->hash_file_name
                && $data['image_source_url'] == \Yii::$app->get('platform')->config()->getCatalogBaseUrl(true)./*Images::getWSCatalogImagesPath(false).*/DIR_WS_IMAGES.'products/'.$this->parentObject->products_id.'/'.$this->products_images_id.'/'.$this->hash_file_name
            ){
                // url to this image description (external image come back)
            }else{
                $this->image_source_url = $data['image_source_url'];
            }
        }

        return $result;
    }

    public function beforeSave($insert)
    {
        if ( $this->hasProperty('use_external_images') && $this->use_external_images ) {

        }else
        if ( !empty($this->image_source_url) || !empty($this->image_data) ) {
            $skip_processing = false;
            // make image local copy
            $targetDir = Images::getFSCatalogImagesPath() . 'products/' . $this->parentObject->products_id . '/' . $this->products_images_id . '/';
            if ( !is_dir($targetDir) ){
                try {
                    FileHelper::createDirectory($targetDir, 0777);
                }catch (\Exception $ex){
                    \Yii::error('AR Image process error CreateDirectory: '.$ex->getMessage());
                }
            }
            $targetNotHashed = tempnam($targetDir,'img');
            if ( !empty($this->image_source_url) ) {
                if ( empty($this->orig_file_name) ) { //??
                    $this->orig_file_name = basename($this->image_source_url);
                }
                if ( preg_match('/^https?:\/\//',$this->image_source_url) ) {
                    if (copy(str_replace(' ','%20', $this->image_source_url), $targetNotHashed, stream_context_create(array('http' => array('protocol_version' => '1.1'))))){
                        $skip_processing = !(is_file($targetNotHashed) && filesize($targetNotHashed)>10);
                    }else{
                        $skip_processing = true;
                    }
                }else {
                    copy($this->image_source_url, $targetNotHashed);
                }
            }elseif (!empty($this->image_data)) {
                file_put_contents($targetNotHashed, $this->image_data);
                unset($this->image_data);
            }
            $orig_file = basename($this->image_source_url);
            if ( !empty($this->orig_file_name) ) {
                $orig_file = $this->orig_file_name;
            }
            // check file change
            $put_new_file = true;
            if ($skip_processing){
                $put_new_file = false;
            }else
            if (!empty($this->hash_file_name)) {
                $imageFilename = Images::getFSCatalogImagesPath() . 'products/' . $this->parentObject->products_id . '/' . $this->products_images_id . '/' . $this->hash_file_name;
                if ( is_file($imageFilename) && ( filesize($imageFilename)==filesize($targetNotHashed) && md5_file($imageFilename)==md5_file($targetNotHashed) ) ) {
                    // need update file - file differ
                    $put_new_file = false;
                }elseif (!is_file($imageFilename)) {

                }
            }
            if ( $put_new_file ) {
                if ( empty($this->hash_file_name) || !preg_match('/^[\da-f]{32}$/',$this->hash_file_name) ) {
                    $this->hash_file_name = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                }
                $newImageFilename = Images::getFSCatalogImagesPath() . 'products/' . intval($this->parentObject->products_id) . '/' . intval($this->products_images_id) . '/' . $this->hash_file_name;
                rename($targetNotHashed, $newImageFilename);
                chmod($newImageFilename,0666);
                $this->generateImageThumbnails = true;
            } else {
                if ($targetNotHashed){
                    try {unlink($targetNotHashed);} catch (\Exception $ex){}
                }
            }
        }
//        if ( !empty($this->image_source_url) ) {
//            $data = @file_get_contents($this->image_source_url, false, stream_context_create(array('http'=>
//                array(
//                    'timeout' => 10,
//                    'header' => array(
//                        "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:53.0) Gecko/20100101 Firefox/53.0\r\n".
//                        "Accept-language: en\r\n" .
//                        "Accept: text/javascript, text/html, application/xml, text/xml, */*\r\n"
//                    ),
//                )
//            )));
//
//            if ( $data ) {
//                $orig_file = basename($this->image_source_url);
//                if ( !empty($this->orig_file_name) ) {
//                    $orig_file = $this->orig_file_name;
//                }
//                if (!empty($this->hash_file_name)) {
//                    $imageFilename = Images::getFSCatalogImagesPath() . 'products/' . $this->parentObject->products_id . '/' . $this->products_images_id . '/' . $this->hash_file_name;
//                    if ( !is_file($imageFilename) || (is_file($imageFilename) && filesize($imageFilename)!=strlen($data)) ) {
//                        $this->image_data = $data;
//                        $this->hash_file_name = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
//                    }
//                }else{
//                    $this->image_data = $data;
//                    $this->hash_file_name = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
//                }
//            }
//        }

        if ( $this->getDirtyAttributes(['hash_file_name','orig_file_name', 'use_origin_image_name']) ) {
            $this->file_name = basename($this->orig_file_name);
        }

        if ( $insert && empty($this->file_name) ) {
            if ( !empty($this->alt_file_name) ) {
                $this->file_name = $this->alt_file_name;
            }else{
                //products_seo_page_name
                if ( !empty($this->orig_file_name) ) {
                    $this->file_name = $this->orig_file_name;
                }
            }
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( is_object($this->parentObject) && $this->parentObject instanceof \common\api\models\AR\Products\Images ){
            if (count($changedAttributes)!=0){
                $this->parentObject->initiateAfterSave('Image::normalize');
            }
        }
    }


    public function beforeDelete()
    {

        if ( defined('TABLE_PRODUCTS_IMAGES_EXTERNAL_URL') ) {
            if (!is_array($this->childCollections['external_urls'])) {
                $this->initCollectionByLookupKey_ExternalUrls(['*']);
            }
            foreach ($this->childCollections['external_urls'] as $externalUrl) {
                $externalUrl->delete();
            }
        }


        return parent::beforeDelete();
    }


}
