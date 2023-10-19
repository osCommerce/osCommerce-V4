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

class Products2Categories extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_to_categories';
    }

    public function getPlatformsCategories()
    {
        return $this->hasMany(PlatformsCategories::class, ['categories_id' => 'categories_id']);
    }

    public function getCategories()
    {
        return $this->hasOne(Categories::class, ['categories_id' => 'categories_id']);
    }

    public function getProducts()
    {
        return $this->hasOne(Products::class, ['products_id' => 'products_id']);
    }

    public function getPlatformsProducts()
    {
        return $this->hasMany(PlatformsProducts::class, ['products_id' => 'products_id']);
    }

    public function getProductsPrices()
    {
        return $this->hasMany(ProductsPrices::class, ['products_id' => 'products_id']);
    }

}