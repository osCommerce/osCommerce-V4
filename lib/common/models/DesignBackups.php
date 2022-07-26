<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_backups".
 *
 * @property int $backup_id
 * @property string|null $date_added
 * @property string $theme_name
 * @property string|null $comments
 */
class DesignBackups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_backups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_added'], 'safe'],
            [['theme_name'], 'required'],
            [['comments'], 'string'],
            [['theme_name'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'backup_id' => 'Backup ID',
            'date_added' => 'Date Added',
            'theme_name' => 'Theme Name',
            'comments' => 'Comments',
        ];
    }
}
