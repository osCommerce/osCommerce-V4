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
 * Class m220810_115020_upd_translations
 */
class m220810_115020_upd_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$this->update('configuration', ['configuration_value'=>'True'], ['configuration_key'=>'EXPRESS_PAYMENTS_HIDE_CHECKOUT']);
		$this->update('translation', ['translation_value'=>'From your account dashboard you can <a href="##URL##account?page_name=order_history"> view your recent<br>orders</a>, <a href="##URL##account?page_name=address_book">manage your shipping and billing addresses</a>, and <a href="##URL##account?page_name=account_edit">edit<br> your password and account details</a>.'], ['translation_key'=>'TEXT_ACCOUNT_INFO', 'translation_entity'=>'main']);
		$this->update('translation', ['translation_value'=>'Welcome to osCommerce platform'], ['translation_key'=>'TEXT_WELCOME_PLATFORM', 'translation_entity'=>'main']);
        $this->update('translation', ['translation_value'=>'Which smart watch is right for me?', 'translation_key'=>'TEXT_WHICH_ITEM'], ['translation_key'=>'TEXT_WHICH_FITBIT', 'translation_entity'=>'main']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220810_115020_upd_translations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220810_115020_upd_translations cannot be reverted.\n";

        return false;
    }
    */
}
