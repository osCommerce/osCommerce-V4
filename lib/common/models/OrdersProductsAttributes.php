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
use common\extensions\ProductDesigner\models as ProductDesignerORM;

class OrdersProductsAttributes extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'orders_products_attributes';
    }

    /*
     * one-to-one
     * @return object
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['orders_id' => 'orders_id']);
    }
}