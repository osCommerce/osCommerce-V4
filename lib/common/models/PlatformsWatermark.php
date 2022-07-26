<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "platforms_watermark".
 *
 * @property integer $platform_id
 * @property integer $status
 * @property string $watermark30
 * @property string $watermark170
 * @property string $watermark300
 * @property string $top_watermark30
 * @property string $top_watermark170
 * @property string $top_watermark300
 * @property string $bottom_watermark30
 * @property string $bottom_watermark170
 * @property string $bottom_watermark300
 * @property string $left_watermark30
 * @property string $left_watermark170
 * @property string $left_watermark300
 * @property string $right_watermark30
 * @property string $right_watermark170
 * @property string $right_watermark300
 * @property string $top_left_watermark30
 * @property string $top_left_watermark170
 * @property string $top_left_watermark300
 * @property string $top_right_watermark30
 * @property string $top_right_watermark170
 * @property string $top_right_watermark300
 * @property string $bottom_left_watermark30
 * @property string $bottom_left_watermark170
 * @property string $bottom_left_watermark300
 * @property string $bottom_right_watermark30
 * @property string $bottom_right_watermark170
 * @property string $bottom_right_watermark300
 */
class PlatformsWatermark extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platforms_watermark';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id'], 'required'],
            [['platform_id', 'status'], 'integer'],
            [['watermark30', 'watermark170', 'watermark300', 'top_watermark30', 'top_watermark170', 'top_watermark300', 'bottom_watermark30', 'bottom_watermark170', 'bottom_watermark300', 'left_watermark30', 'left_watermark170', 'left_watermark300', 'right_watermark30', 'right_watermark170', 'right_watermark300', 'top_left_watermark30', 'top_left_watermark170', 'top_left_watermark300', 'top_right_watermark30', 'top_right_watermark170', 'top_right_watermark300', 'bottom_left_watermark30', 'bottom_left_watermark170', 'bottom_left_watermark300', 'bottom_right_watermark30', 'bottom_right_watermark170', 'bottom_right_watermark300'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'status' => 'Status',
            'watermark30' => 'Watermark30',
            'watermark170' => 'Watermark170',
            'watermark300' => 'Watermark300',
            'top_watermark30' => 'Top Watermark30',
            'top_watermark170' => 'Top Watermark170',
            'top_watermark300' => 'Top Watermark300',
            'bottom_watermark30' => 'Bottom Watermark30',
            'bottom_watermark170' => 'Bottom Watermark170',
            'bottom_watermark300' => 'Bottom Watermark300',
            'left_watermark30' => 'Left Watermark30',
            'left_watermark170' => 'Left Watermark170',
            'left_watermark300' => 'Left Watermark300',
            'right_watermark30' => 'Right Watermark30',
            'right_watermark170' => 'Right Watermark170',
            'right_watermark300' => 'Right Watermark300',
            'top_left_watermark30' => 'Top Left Watermark30',
            'top_left_watermark170' => 'Top Left Watermark170',
            'top_left_watermark300' => 'Top Left Watermark300',
            'top_right_watermark30' => 'Top Right Watermark30',
            'top_right_watermark170' => 'Top Right Watermark170',
            'top_right_watermark300' => 'Top Right Watermark300',
            'bottom_left_watermark30' => 'Bottom Left Watermark30',
            'bottom_left_watermark170' => 'Bottom Left Watermark170',
            'bottom_left_watermark300' => 'Bottom Left Watermark300',
            'bottom_right_watermark30' => 'Bottom Right Watermark30',
            'bottom_right_watermark170' => 'Bottom Right Watermark170',
            'bottom_right_watermark300' => 'Bottom Right Watermark300',
        ];
    }
}
