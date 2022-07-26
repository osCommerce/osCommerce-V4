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

class ProductsAssetsValues extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_assets_values';
    }
    
    public function getAssetFields(){
        return $this->hasOne(ProductsAssetsFields::className(), ['products_assets_fields_id' => 'products_assets_fields_id']);
    }
    
    public function getAsset(){
        return $this->hasOne(ProductsAssets::className(), ['products_assets_id' => 'products_assets_id']);
    }
}