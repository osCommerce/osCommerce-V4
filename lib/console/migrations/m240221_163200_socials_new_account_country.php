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
 * Class m240221_163200_socials_new_account_country
 */
class m240221_163200_socials_new_account_country extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'SOCIALS_NEW_ACCOUNT_COUNTRY',
            'configuration_title' => 'Social Logins New Account Country',
            'configuration_description' => 'Set the country for new accounts created via Social Logins (Googla, Facebook etc).',
            'configuration_group_id' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
            'configuration_value' => 'Platform',
            'sort_order' => '77777',
            'set_function' => "tep_cfg_select_option(array('Platform', 'Empty'),",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m240221_163200_socials_new_account_country cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240221_163200_socials_new_account_country cannot be reverted.\n";

        return false;
    }
    */
}
