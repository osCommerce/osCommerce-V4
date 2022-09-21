<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouses_products_layers".
 *
 * @property int $layers_id
 * @property string $layers_name
 * @property date $expiry_date
 */
class WarehousesProductsLayers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_products_layers';
    }

}
