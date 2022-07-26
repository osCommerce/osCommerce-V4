<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "socials".
 *
 * @property int $socials_id
 * @property int $platform_id
 * @property string $module
 * @property string $client_id
 * @property string $client_secret
 * @property int $active
 * @property int $test_success
 * @property string $link
 * @property string $image
 * @property string $css_class
 */
class Socials extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'socials';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'module', 'client_id', 'client_secret', 'active'], 'required'],
            [['platform_id', 'active', 'test_success'], 'integer'],
            [['module'], 'string', 'max' => 32],
            [['client_id', 'client_secret', 'link', 'image', 'css_class'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'socials_id' => 'Socials ID',
            'platform_id' => 'Platform ID',
            'module' => 'Module',
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
            'active' => 'Active',
            'test_success' => 'Test Success',
            'link' => 'Link',
            'image' => 'Image',
            'css_class' => 'CSS Class',
        ];
    }
}
