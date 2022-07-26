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
/**
 * This is the model class for table "warehouses_orders_products".
 *
 * @property int $warehouse_id
 * @property int $orders_id
 * @property int $products_id
 * @property string $products_model
 * @property int $products_quantity
 * @property int $not_available_quantity
 * @property string $uprid
 * @property string $template_uprid
 * @property int $suppliers_id
 * @property int $purchase_orders_id
 * @property int $purchase_orders_quantity
 */
class WarehousesOrdersProducts extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_orders_products';
    }
   
}
