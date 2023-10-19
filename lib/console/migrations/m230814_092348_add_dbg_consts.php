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
 * Class m230814_092348_add_dbg_consts
 */
class m230814_092348_add_dbg_consts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'DEFINE_DBG_CONST',
            'configuration_title' => 'Define custom debug constants',
            'configuration_description' => 'Define custom debug constants for subsystems and extensions ("," as separator)',
            'configuration_group_id' => '',
            'configuration_value' => '',
            'sort_order' => '100',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230814_092348_add_dbg_consts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230814_092348_add_dbg_consts cannot be reverted.\n";

        return false;
    }
    */
}
