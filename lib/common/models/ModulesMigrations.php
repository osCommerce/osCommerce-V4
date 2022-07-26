<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "modules_migrations".
 *
 * @property int $migration_id
 * @property string $type
 * @property string $code
 * @property float $ver_from
 * @property float $ver_to
 * @property string $classname
 * @property string|null $result
 * @property string|null $applied_time
 * @property int $applied_admin_id
 */
class ModulesMigrations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'modules_migrations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'classname', 'applied_admin_id'], 'required'],
            [['ver_from', 'ver_to'], 'number'],
            [['result'], 'string'],
            [['applied_time'], 'safe'],
            [['applied_admin_id'], 'integer'],
            [['type'], 'string', 'max' => 32],
            [['code'], 'string', 'max' => 128],
            [['classname'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'migration_id' => 'Migration ID',
            'type' => 'Type',
            'code' => 'Code',
            'ver_from' => 'Ver From',
            'ver_to' => 'Ver To',
            'classname' => 'Classname',
            'result' => 'Result',
            'applied_time' => 'Applied Time',
            'applied_admin_id' => 'Applied Admin ID',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)){
            return false;
        }
        if ( $insert ){
            if (is_null($this->applied_admin_id)) {
                $this->applied_admin_id = (int)($_SESSION['login_id']??0);
            }
            if ( empty($this->applied_time) ) {
                $this->applied_time = new \yii\db\Expression('NOW()');
            }
        }
        return true;
    }

}
