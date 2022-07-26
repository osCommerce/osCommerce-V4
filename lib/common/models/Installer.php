<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "installer".
 *
 * @property int $installer_id
 * @property string|null $filename
 * @property string|null $data
 * @property string $date_added
 */
class Installer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'installer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data'], 'string'],
            [['date_added'], 'required'],
            [['date_added'], 'safe'],
            [['filename'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'installer_id' => 'Installer ID',
            'filename' => 'Filename',
            'data' => 'Data',
            'date_added' => 'Date Added',
        ];
    }
}
