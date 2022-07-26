<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "page_status_switch".
 *
 * @property int $page_status_switch_id
 * @property int $action
 * @property int $period
 * @property string|null $date
 */
class PageStatusSwitch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page_status_switch';
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
            'page_status_switch_id' => 'Page Status Switch ID',
            'page_status_id' => 'Page Status ID',
            'status' => 'Status',
            'period' => 'Period',
            'date' => 'Date',
        ];
    }
}
