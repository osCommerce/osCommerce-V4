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

use common\classes\Images;
use common\helpers\Image;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

class ProductsImagesDescription extends ActiveRecord
{
    public $_imageUri;
    /**
     * @var \common\models\File\Upload
     */
    protected $uploadedImage;

    public $parentObject;

    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_images_description';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getImage()
    {
        return $this->hasOne(ProductsImages::className(), ['products_images_id' => 'products_images_id']);
    }

    public function getImageUri()
    {
        if ( is_null($this->_imageUri) ) {
            $this->_imageUri = '';
            if (!empty($this->hash_file_name)) {
                if ( is_object($this->parentObject) && $this->parentObject->products_id ) {
                    $this->_imageUri = 'products/' . $this->parentObject->products_id . '/' . $this->products_images_id . '/' . $this->hash_file_name;
                }else {
                    $imageObj = $this->getImage()->one();
                    if ($imageObj && $imageObj->products_id) {
                        $this->_imageUri = 'products/' . $imageObj->products_id . '/' . $this->products_images_id . '/' . $this->hash_file_name;
                    }
                }
            }
        }
        return $this->_imageUri;
    }

    public function setImageUri($value)
    {
        if ( is_object($value) && $value instanceof \common\models\File\Upload) {
            $this->uploadedImage = $value;
        }
    }

    public function beforeSave($insert)
    {
        if ( $this->uploadedImage ) {
            if ( empty($this->orig_file_name) ) {
                if ( strpos($this->uploadedImage->sourceFile,'/')!==false ) {
                    $this->orig_file_name = substr($this->uploadedImage->sourceFile, strrpos($this->uploadedImage->sourceFile, '/'));
                }else{
                    $this->orig_file_name = $this->uploadedImage->sourceFile;
                }
            }
            if ( empty($this->hash_file_name) ) {
                $this->hash_file_name = md5($this->orig_file_name . "_" . date('dmYHis') . "_" . microtime(true).rand(1000,9999));
            }

            $newFilename = Images::getFSCatalogImagesPath().'products/'.intval($this->parentObject->products_id).'/'.intval($this->parentObject->products_images_id).'/'.$this->hash_file_name;

            if ( !is_dir(dirname($newFilename)) ){
                try {
                    FileHelper::createDirectory(dirname($newFilename), 0777);
                }catch (\Exception $ex){}
            }

            @copy($this->uploadedImage->sourceFile, $newFilename);
            if ( is_file($newFilename) ) @chmod($newFilename,0666);

            Images::normalizeImageFiles(intval($this->parentObject->products_id), intval($this->parentObject->products_images_id));
        }
        return parent::beforeSave($insert);
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //echo '<pre>IMAGE DESC '; var_dump($this); echo '</pre>';
    }

}