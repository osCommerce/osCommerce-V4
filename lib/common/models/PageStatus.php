<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "page_status".
 *
 * @property int $page_status_id
 * @property string|null $type
 * @property int $page_id
 * @property int $publish
 */
class PageStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'page_status_id' => 'Page Status ID',
            'type' => 'Type',
            'page_id' => 'Page ID',
            'status' => 'Status'
        ];
    }
}
