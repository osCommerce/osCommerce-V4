<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "easy_passwords".
 *
 * @property string $password
 */
class EasyPasswords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'easy_passwords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password'], 'required'],
            [['password'], 'string', 'max' => 32],
            [['password'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'password' => 'Password',
        ];
    }
}
