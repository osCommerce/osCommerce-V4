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


use yii\db\ActiveRecord;

class OptionsTemplates extends ActiveRecord
{

    public static function tableName()
    {
        return 'options_templates';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts2OptionsTemplates()
    {
        return $this->hasMany(Products2OptionsTemplates::class, ['options_templates_id' => 'options_templates_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::className(), ['products_id' => 'products_id'])->via('products2OptionsTemplates');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsOptions()
    {
        return $this->hasMany(ProductsOptions::className(), ['products_id' => 'products_id'])->via('products2OptionsTemplates');
    }



}