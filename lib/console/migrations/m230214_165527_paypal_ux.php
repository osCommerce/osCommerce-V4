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

use common\classes\Migration;

/**
 * Class m230214_165527_paypal_ux
 */
class m230214_165527_paypal_ux extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('payment', [
          'MODULE_PAYMENT_PAYPAL_PARTNER_NOT_APPLICABLE_COUNTRY' => '* not applicable in country of your sale channel',
          'MODULE_PAYMENT_PAYPAL_PARTNER_GIROPAY_TITLE' => 'Giropay',
          'MODULE_PAYMENT_PAYPAL_PARTNER_APMS_SHOW_FIELDS' => 'Set "Show PayPal button on Checkout" to "Fields" to offer APMs',
          'MODULE_PAYMENT_PAYPAL_PARTNER_API_TEST' => 'Test API Details',
          'MODULE_PAYMENT_PAYPAL_PARTNER_API_OK' => 'Test connect OK',
          'MODULE_PAYMENT_PAYPAL_PARTNER_API_FAIL' => 'Test connect Failed',
          'MODULE_PAYMENT_PAYPAL_PARTNER_API_FAIL_DATA' => 'Test connect Failed (incorrect )',
        ]);


        $this->db->createCommand(
            "update translation set translation_value='Are you sure you want to connect different PayPal account?' where translation_key in ('TEXT_PAYPAL_PARTNER_UNLINK_PROMPT') and translation_entity='admin/main'"

        )->execute();

        if (\common\models\PlatformsConfiguration::find()->andWhere(['like', 'configuration_key', 'MODULE_PAYMENT_PAYPAL_PRO_HS_%', false])->count() == 0) {
            \common\models\Translation::deleteAll('translation_key like "MODULE_PAYMENT_PAYPAL_PRO_HS_%" and translation_entity="payment" ');
        }

        if ($this->isTableExists('paypal_seller_info') && !$this->isFieldExists('status', 'paypal_seller_info')) {
            $this->addColumnIfMissing('paypal_seller_info', 'status', $this->integer(11)->notNull()->defaultValue(0));
            common\modules\orderPayment\lib\PaypalPartner\models\SellerInfo::updateAll(['status' => 1]);
        }


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230214_165527_paypal_ux cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230214_165527_paypal_ux cannot be reverted.\n";

        return false;
    }
    */
}
