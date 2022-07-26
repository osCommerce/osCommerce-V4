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

class Products2OptionsTemplates extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_to_options_templates';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::class, ['products_id' => 'products_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptionsTemplates()
    {
        return $this->hasMany(OptionsTemplates::class, ['options_templates_id' => 'options_templates_id']);
    }

}