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
 * Class m221017_042146_config_checkout_btn
 */
class m221017_042146_config_checkout_btn extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
       $this->insert('configuration',[
            'configuration_title' => 'Checkout button pay with card',
            'configuration_key' => 'CHECKOUT_BTN_TEXT',
            'configuration_value' => 'True',
            'configuration_description' => 'Checkout button pay with card',
            'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
            'sort_order' => 35,
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
        ]);
		
		$this->addTranslation('main', [
          'TEXT_PAY_NOW' => 'Pay Now',
		  'TEXT_PAY_WITH_CARD' => 'Pay with card',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221017_042146_config_checkout_btn cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221017_042146_config_checkout_btn cannot be reverted.\n";

        return false;
    }
    */
}
