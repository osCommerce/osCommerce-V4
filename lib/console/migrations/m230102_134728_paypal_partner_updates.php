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
 * Class m230102_134728_paypal_partner_updates
 */
class m230102_134728_paypal_partner_updates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->db->createCommand("update translation set translation_value='Connect different PayPal account' where translation_key='PAYPAL_PARTNER_DELETE_SELLER' and translation_value='Disconnect PayPal seller'")->execute();
        $this->addTranslation('payment', [
          'TEXT_PAYPAL_PARTNER_SAVE_TO_CONTINUE' => 'PayPal transaction server has been changed. Save changes to finish API configuration',
          'TEXT_PAYPAL_PARTNER_CONTINUE_PAYPAL' => 'Click PayPal button below and continue boarding in PayPal\'s  minibrowser',
          'PAYPAL_PARTNER_SELLER_EMAIL_SAVE_NOTE' => '<strong style="color:red">Update settings to save your PayPal email address</strong>',
          
        ]);
        $this->addTranslation('admin/main', [
          'TEXT_ADVANCED' => 'Advanced...',
          'PAYPAL_ACCOUNT_OPTIONS_NO' => 'No, create new one'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230102_134728_paypal_partner_updates cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230102_134728_paypal_partner_updates cannot be reverted.\n";

        return false;
    }
    */
}
