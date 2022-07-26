<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "young_customers".
 *
 * @property integer $id
 * @property string $email
 * @property string $expiration_date
 */
class YoungCustomers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'young_customers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'expiration_date'], 'required'],
            [['expiration_date'], 'safe'],
            [['email'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'expiration_date' => 'Expiration Date',
        ];
    }
}
