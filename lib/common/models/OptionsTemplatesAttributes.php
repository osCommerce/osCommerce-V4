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

/**
 * Class OptionsTemplatesAttributes
 * @package common\models
 *
 * @property int $options_templates_attributes_id
 * @property int $options_templates_id
 * @property int $options_id
 * @property int $options_values_id
 *
 */
class OptionsTemplatesAttributes extends ActiveRecord
{

    public static function tableName()
    {
        return 'options_templates_attributes';
    }

    public function getPrices(){
        return $this->hasMany(OptionsTemplatesAttributesPrices::className(),['options_templates_attributes_id' => 'options_templates_attributes_id']);
    }

    public function getProductsOptions()
    {
        return $this->hasMany(\common\models\ProductsOptions::class, ['products_options_id' => 'options_id']);
    }

    public function getProductsOptionsValues()
    {
        return $this->hasMany(\common\models\ProductsOptionsValues::class, ['products_options_values_id' => 'options_values_id']);
    }


    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        OptionsTemplatesAttributesPrices::deleteAll(['options_templates_attributes_id'=>$this->options_templates_attributes_id]);

        return true;
    }


}