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

class ProductsOptionsValues extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_options_values';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValues2Options()
    {
        return $this->hasMany(ProductsOptions2ProductsOptionsValues::class, ['products_options_values_id' => 'products_options_values_id']);
    }

    /**
     * many-to-many
     * @return array
     */
    public function getProductsOptions()
    {
        return $this->hasMany(ProductsOptions::class, ['products_options_id' => 'products_options_id'])->via('values2Options');
    }

    /**
     * many-to-many
     * @return array
     */
    public function getProductsAttributes()
    {
        return $this->hasMany(ProductsAttributes::class, ['options_values_id' => 'products_options_values_id']);
    }


    public static function nextID()
    {
        return static::find()->max('products_options_values_id') + 1;
    }
}