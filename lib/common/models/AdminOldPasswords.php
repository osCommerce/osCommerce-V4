<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_old_passwords".
 *
 * @property int $pass_id
 * @property int $admin_id
 * @property string $password
 * @property string|null $date_added
 */
class AdminOldPasswords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_old_passwords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'password'], 'required'],
            [['admin_id'], 'integer'],
            [['date_added'], 'safe'],
            [['password'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pass_id' => 'Pass ID',
            'admin_id' => 'Admin ID',
            'password' => 'Password',
            'date_added' => 'Date Added',
        ];
    }

    public static function isOld($adminId = 0, $password = '')
    {
        $return = false;
        $passwordCheckLimit = 5;
        try {
            foreach (self::find()
                ->where(['admin_id' => (int)$adminId])
                ->orderBy(['pass_id' => SORT_DESC])
                ->limit($passwordCheckLimit)
                ->asArray(true)->each() as $aopRecord
            ) {
                try {
                    if (\common\helpers\Password::validate_password($password, $aopRecord['password'], 'backend')) {
                        $return = true;
                        break;
                    }
                } catch (\Exception $exc) {
                    \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminOldPasswordsIsOldValidate');
                }
            }
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminOldPasswordsIsOld');
        }
        unset($passwordCheckLimit);
        unset($aopRecord);
        unset($password);
        unset($adminId);
        return $return;
    }

    public static function addOld($adminId = 0, $password = '')
    {
        try {
            $aopRecord = new self();
            $aopRecord->loadDefaultValues();
            $aopRecord->admin_id = (int)$adminId;
            $aopRecord->password = \common\helpers\Password::encrypt_password($password, 'backend');
            $aopRecord->date_added = date('Y-m-d H:i:s');
            $aopRecord->save(false);
            unset($password);
            unset($adminId);
            return true;
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminOldPasswordsAddOld');
        }
        return false;
    }
}