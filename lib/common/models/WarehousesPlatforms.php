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
 * This is the model class for table "warehouses_to_platforms".
 *
 * @property int $warehouse_id
 * @property int $platform_id
 * @property int $status
 * @property int $sort_order 
 */
class WarehousesPlatforms extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_to_platforms';
    }
   
}
