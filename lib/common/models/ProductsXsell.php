<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_xsell".
 *
 * @property int $ID
 * @property int $products_id
 * @property int $xsell_id
 * @property int $sort_order
 */
class ProductsXsell extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products_xsell';
    }



}
