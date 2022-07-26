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

class ProductsImagesAttributes extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_images_attributes';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getImage()
    {
        return $this->hasOne(ProductsImages::className(), ['products_images_id' => 'products_images_id']);
    }

    /**
     * one-to-Many
     * @return array
     */
    public function getOptions()
    {
        return $this->hasOne(ProductsOptions::className(), ['products_options_id' => 'products_options_id']);
    }

    /**
     * one-to-Many
     * @return array
     */
    public function getOptionsValues()
    {
        return $this->hasOne(ProductsOptionsValues::className(), ['products_options_values_id' => 'products_options_values_id']);
    }
}