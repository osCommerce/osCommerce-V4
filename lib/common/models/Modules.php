<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "modules".
 *
 * @property int $module_id
 * @property string $type
 * @property string $code
 * @property float $version
 * @property float|null $version_db
 * @property int $state 0-not installed,1-installed,2-enabled
 * @property string|null $installed_time
 * @property int $installed_admin_id
 * @property string|null $changed_time
 * @property int $changed_admin_id
 */
class Modules extends \yii\db\ActiveRecord
{
    public const STATE_UNINSTALLED = 0;
    public const STATE_INSTALLED = 1;
    public const STATE_ENABLED = 2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'installed_admin_id', 'changed_admin_id'], 'required'],
            [['version', 'version_db'], 'number'],
            [['state', 'installed_admin_id', 'changed_admin_id'], 'integer'],
            [['installed_time', 'changed_time'], 'safe'],
            [['type'], 'string', 'max' => 32],
            [['code'], 'string', 'max' => 128],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Module ID',
            'type' => 'Type',
            'code' => 'Code',
            'version' => 'Version',
            'version_db' => 'Version Db',
            'state' => 'State',
            'installed_time' => 'Installed Time',
            'installed_admin_id' => 'Installed Admin ID',
            'changed_time' => 'Changed Time',
            'changed_admin_id' => 'Changed Admin ID',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)){
            return false;
        }
        if ( $insert ){
            if (is_null($this->installed_admin_id)) {
                $this->installed_admin_id = (int)($_SESSION['login_id']??0);
            }
            if ( empty($this->installed_time) ) {
                $this->installed_time = new \yii\db\Expression('NOW()');
            }
        } else {
            if (is_null($this->changed_admin_id)) {
                $this->changed_admin_id = (int)($_SESSION['login_id']??0);
            }
            if ( empty($thischanged_time) ) {
                $this->changed_time = new \yii\db\Expression('NOW()');
            }
        }
        return true;
    }

}
