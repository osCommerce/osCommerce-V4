<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "reviews".
 *
 * @property integer $reviews_id
 * @property integer $products_id
 * @property integer $customers_id
 * @property string $customers_name
 * @property integer $reviews_rating
 * @property string $date_added
 * @property string $last_modified
 * @property integer $reviews_read
 * @property integer $status
 * @property integer $new
 */
class Reviews extends ActiveRecord
{
  public $average_rating=0;
  /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{reviews}}';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getProduct() {
        return $this->hasOne(Products::className(), ['products_id' => 'products_id']);
    }

    /**
     * one-to-one
     * @return object
     */
    public function getDescriptions() {
        return $this->hasMany(ReviewsDescription::className(), ['reviews_id' => 'reviews_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $changed = $this->getDirtyAttributes(['status', 'new']);
        if ( !isset($changed['new']) && isset($changed['status']) && (int)$changed['status']!=intval($this->getOldAttribute('status')) ){
            $this->new = 0;
        }

        return true;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach ($this->descriptions as $description){
            $description->delete();
        }
        return true;
    }


}
