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
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ProductsImages extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_images';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getProduct()
    {
        return $this->hasOne(Products::className(), ['products_id' => 'products_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getDescription()
    {
        return $this->hasMany(ProductsImagesDescription::className(), ['products_images_id' => 'products_images_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getImagesAttributes()
    {
        return $this->hasMany(ProductsImagesAttributes::className(), ['products_images_id' => 'products_images_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getExternalUrl()
    {
        return $this->hasMany(ProductsImagesExternalUrl::className(), ['products_images_id' => 'products_images_id']);
    }

    /**
     * one-to-many
     * @return array
     */
    public function getInventory()
    {
        return $this->hasMany(ProductsImagesInventory::className(), ['products_images_id' => 'products_images_id']);
    }

    /**
     * Current frontend description (current language)
     * @return actionQuery
     */
    public function getImageListDescription()
    {
      $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasMany(
            ProductsImagesDescription::className(), ['products_images_id' => 'products_images_id'])
            //->select('*')
            //->leftJoin(TABLE_PRODUCTS_IMAGES_EXTERNAL_URL, '')
            ->where(['and', "(file_name !='' or alt_file_name!='')",  ['language_id' => [(int)$languages_id, 0]] ])
            ->orderBy(['language_id' => SORT_DESC])

            ;

    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        Images::removeProductImage($this->products_id, $this->products_images_id);
        /*
        $related = $this->getDescription()->all();
        foreach ($related as $relatedModel) {
            $relatedModel->delete();
        }
        */

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //echo '<pre>IMAGE '; var_dump($this); echo '</pre>';
    }

}