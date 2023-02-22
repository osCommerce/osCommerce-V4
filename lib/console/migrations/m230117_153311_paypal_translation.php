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
 * Class m230117_153311_paypal_translation
 */
class m230117_153311_paypal_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
          'PAYPAL_SANDBOX_TRY' => 'Do you want to try sandbox first?<br>Remember - you can switch to production at any time',
          'PAYPAL_ACCOUNT_PRESS_BUTTON' => 'Press the "PayPal" button',
          'PAYPAL_GET_DATA' => 'To get data from paypal',
          'TEXT_ENTER_API_DETAILS' => 'Enter api details',
          'PAYPAL_PARTNER_GET_DATA_PAYPAL' => 'to get data from PayPal',
          
        ]);

        $this->db->createCommand("update translation set translation_value='Use PayPal sandbox account (you could switch to live account later)' where translation_key='PAYPAL_SANDBOX_MODE' and translation_value like 'Try PayPal test (sandbox) account (you could switch to live account later)'")->execute();
        $this->db->createCommand("update translation set translation_value='I have my own REST API keys on hand, and I know how to set them up' where translation_key='PAYPAL_ACCOUNT_OPTIONS_OWN_API_ACCESS' and translation_value like 'Yes, and I have own REST API access'")->execute();

        $this->db->createCommand("update platforms_configuration
            SET set_function = 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'Fields\', \'False\'), '
            WHERE  configuration_key='MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT'
            AND set_function = 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '
            ")->execute();

        $this->addTranslation('payment', [
          'PAYPAL_PARTNER_TEXT_CC_NUMBER' => 'Card Number',
          'PAYPAL_PARTNER_TEXT_EXP'  => 'Expiration Date',
          'PAYPAL_PARTNER_TEXT_CVV'  => 'CVV',
          'PAYPAL_PARTNER_TEXT_NAME_ON_CARD'  => 'Name on Card',
          'PAYPAL_PARTNER_TEXT_EXP_PLACEHOLDER'  => 'MM/YY',
          'PAYPAL_PARTNER_TEXT_ERROR_CAPTURE'  => 'Payment could not be captured.',
          'PAYPAL_PARTNER_TEXT_ADVANCED_SETTINGS'  => 'Account details and advanced settings',
          'PAYPAL_PARTNER_TEXT_ACCOUNT_DETAILS'  => 'PayPal Account details',
          'PAYPAL_PARTNER_TEXT_SETTINGS'  => 'Checkout Fields settings',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS'  => 'Webhooks',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBED'  => 'Subscribed Webhooks',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBE'  => 'Subscribe',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBE_CONFIRM'  => 'Please Confirm you want to subscribe to required Webhooks. You can unsubscribe at any time in you PayPal account',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS_REQUIRED'  => 'Required Webhooks',
          'TEXT_PAYPAL_PARTNER_WEBHOOKS_REQUIRED_NOTE'  => ' * for alternative payment methods (APM)',
          ]);
        
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230117_153311_paypal_translation cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230117_153311_paypal_translation cannot be reverted.\n";

        return false;
    }
    */
}
