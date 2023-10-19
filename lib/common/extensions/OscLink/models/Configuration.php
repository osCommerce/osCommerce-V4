<?php

namespace common\extensions\OscLink\models;

use Yii;

/**
 * This is the model class for table "connector_osclink_configuration".
 *
 * @property string $cmc_key
 * @property string $cmc_value
 * @property string $cmc_upd_date
 * @property int $cmc_upd_admin
 */
class Configuration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'connector_osclink_configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cmc_key', 'cmc_upd_admin'], 'required'],
            [['cmc_upd_date'], 'safe'],
            [['cmc_upd_admin'], 'integer'],
            [['cmc_key'], 'string', 'max' => 128],
            [['cmc_value'], 'string', 'max' => 250],
            [['cmc_key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cmc_key' => 'Cmc Key',
            'cmc_value' => 'Cmc Value',
            'cmc_upd_date' => 'Cmc Upd Date',
            'cmc_upd_admin' => 'Cmc Upd Admin',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->cmc_upd_date = date('Y-m-d H:i:s');
        $this->cmc_upd_admin = \Yii::$app->session->get('login_id');
        return true;
    }

    public static function findCancelSign()
    {
        return self::findOne('.cancel_sign');
    }

    public static function isCancelSign()
    {
        $row = self::findCancelSign();
        return !empty($row) && $row->cmc_value == 'TRUE';
    }

    public static function throwIfCanceled()
    {
        if (self::isCancelSign()) {
            \OscLink\Logger::print('Cancel sign detected');
            throw new \yii\base\UserException('Process was canceled by user.');
            //throw new \Exception('Process was canceled by user.');
        }
    }

    public static function createCancelSign()
    {
        $row = self::findCancelSign();
        if (empty($row)) {
            $row = new self();
            $row->cmc_key = '.cancel_sign';
        }
        $row->cmc_value = 'TRUE';
        $row->save(false);
    }

    public static function deleteCancelSign()
    {
        $row = self::findCancelSign();
        if (!empty($row)) {
            $row->delete();
        }
    }

}