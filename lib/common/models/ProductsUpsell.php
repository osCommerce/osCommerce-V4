<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_upsell".
 *
 * @property int $ID
 * @property int $products_id
 * @property int $upsell_id
 * @property int $sort_order
 */
class ProductsUpsell extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products_upsell';
    }



}
