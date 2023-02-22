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
 * Class m230212_194528_paypal_updates
 */
class m230212_194528_paypal_updates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('payment', [
            'PAYPAL_PARTNER_LEGAL_NAME' => 'Legal name',
            'PAYPAL_PARTNER_SELLER_MERCHANT_ID' => 'Merchant id',
            'PAYPAL_PARTNER_PAYMENTS_RECEIVABLE' => 'Payments receivable',
            'PAYPAL_PARTNER_PRIMARY_EMAIL' => 'Primary e-mail',
            'PAYPAL_PARTNER_PRIMARY_CURRENCY' => 'Primary currency',


          'PAYPAL_PARTNER_CAPABILITIES' => 'Capabilities',
            'PAYPAL_PARTNER_CARD_PROCESSING_VIRTUAL_TERMINAL_TEXT' => 'Virtual terminal',
            'PAYPAL_PARTNER_COMMERCIAL_ENTITY_TEXT' => 'Commercial',
            'PAYPAL_PARTNER_CUSTOM_CARD_PROCESSING_TEXT' => 'Custom card processing',
            'PAYPAL_PARTNER_DEBIT_CARD_SWITCH_TEXT' => 'Debit card switch',
            'PAYPAL_PARTNER_FRAUD_TOOL_ACCESS_TEXT' => 'Fraud tool',
            'PAYPAL_PARTNER_ALT_PAY_PROCESSING_TEXT' => 'Alternative payment methods',
            'PAYPAL_PARTNER_RECEIVE_MONEY_TEXT' => 'Receive money',
            'PAYPAL_PARTNER_SEND_MONEY_TEXT' => 'Send money',
            'PAYPAL_PARTNER_STANDARD_CARD_PROCESSING_TEXT' => 'Standard card processing',
            'PAYPAL_PARTNER_WITHDRAW_MONEY_TEXT' => 'Withdraw money',

          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING_TITLE' => 'Enable payment methods',
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING_DESCRIPTION' => 'Enable payment methods',
        ]);

        $this->db->createCommand(
            "update translation set translation_value='Enable payment methods' where translation_key in ('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING_DESCRIPTION', 'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING_TITLE') and translation_entity='payment'"

        )->execute();

        $this->db->createCommand(
            "update translation set translation_value='ATTENTION. Your PayPal account will be disconnected now. <br>Disconnecting your PayPal account will prevent you from offering PayPal on your website. <br>You\'ll need access to PayPal account or PayPal developer account to offer PayPal on website again. <br>You could export current settings for backup (click No and use appropriate buttons on listing).<br><br>Do you wish to continue?' where translation_key in ('TEXT_PAYPAL_PARTNER_UNLINK_PROMPT') and translation_entity='admin/main'"

        )->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230212_194528_paypal_updates cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230212_194528_paypal_updates cannot be reverted.\n";

        return false;
    }
    */
}
