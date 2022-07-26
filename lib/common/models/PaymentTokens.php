<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\models;

use Yii;

/**
 * This is the model class for table "payment_tokens".
 *
 * @property int $payment_tokens_id
 * @property int $customers_id
 * @property string $payment_class
 * @property string $token
 * @property int $is_default 
 */
class PaymentTokens extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customers_id'], 'integer'],
            [['payment_class'], 'string', 'max' => 127],
            [['card_name'], 'string', 'max' => 200],
            [['card_type'], 'string', 'max' => 20],
            [['last_digits'], 'string', 'max' => 20],
            [['exp_date'], 'string', 'max' => 10],
            [['token'], 'string', 'max' => 8096],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'payment_tokens_id' => 'Payment Tokens ID',
            'customers_id' => 'Customers ID',
            'payment_class' => 'Payment Class',
            'card_name' => 'CC name (customers)',
            'last_digits' => 'CC number masked',
            'exp_date' => 'Expiration date',
            'token' => 'Token',
        ];
    }

    public function beforeSave($insert) {
      $this->token = utf8_encode(\Yii::$app->security->encryptByKey( $this->token, $this->getKey()));
      if (empty($this->is_default)) {
        $tokensCount = self::find()->andWhere(['customers_id' => $this->customers_id, 'payment_class' => $this->payment_class])->count();
        if ($tokensCount==0) {
          $this->is_default = 1;
        }
      }
      return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes) {
      if (!empty($this->is_default) && ($insert || (isset($changedAttributes['is_default']) && $changedAttributes['is_default']==0) ) ) {
        self::updateAll(['is_default' => 0],
            ['and',
              ['customers_id' => $this->customers_id, 'payment_class' => $this->payment_class],
              ['<>',  'payment_tokens_id', $this->payment_tokens_id]
              ]);
      }
      return parent::afterSave($insert, $changedAttributes);
    }

    public function afterFind() {
      $this->token = \Yii::$app->security->decryptByKey( utf8_decode($this->token), $this->getKey());
      parent::afterFind();
    }

    private function getKey(){
      $key = 'p?h5ai6R=UOd%RfMVC]`jp::k=@D)_M#4Mi^a+';
      if (!empty(Yii::$app->params['paymentTokensEncryptKey']) && strlen(trim(Yii::$app->params['paymentTokensEncryptKey']))>8) {
        $key = Yii::$app->params['paymentTokensEncryptKey'];
      }
      return $key;

    }

/**
 *
 * @param int $cId
 * @param string $paymentClass
 * @param string $token not encoded
 * @return int number of rows deleted
 */
    public static function deleteToken($cId, $paymentClass, $token) {
      $ret = false;
      $models = self::find()
          ->where([
            'customers_id' => $cId,
            'payment_class' => $paymentClass
          ])->all();
      foreach ($models as $model) {
        if ($model->token == $token) {
          $md = false;
          if ($model->is_default) {
            $md = self::find()
            ->where([
              'customers_id' => $cId,
              'payment_class' => $paymentClass
            ])->
            andWhere(['<>', 'payment_tokens_id', $model->payment_tokens_id])->
            limit(1)->one();
          }

          $model->delete();

          if ($md) {
            $md->is_default = 1;
            $md->save(false);
          }
          
          $ret = true;
          break;
        }
      }
      return $ret;
    }


}
