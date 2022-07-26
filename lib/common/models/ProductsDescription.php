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

use backend\models\ProductNameDecorator;
use Yii;
use yii\db\ActiveRecord;
use common\extensions\ProductDesigner\models as ProductDesignerORM;

class ProductsDescription extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_description';
    }

    /**
     * many-to-one
     * @return array
     */
    public function getProduct()
    {
        return $this->hasMany(Products::className(), ['products_id' => 'products_id']);
    }
    
    public static function create($products_id, $language_id, $platform_id, $department_id = 0, $fields = []){
        $new = new static();
        $new->products_id = (int)$products_id;
        $new->language_id = (int)$language_id;
        $new->platform_id = (int)$platform_id;
        $new->department_id = (int)$department_id;
        return $new;
    }

    public function getBackendListingName()
    {
        if (ProductNameDecorator::instance()->useInternalNameForListing() && !empty($this->products_internal_name)){
            return $this->products_internal_name;
        }
        return $this->products_name;
    }
}