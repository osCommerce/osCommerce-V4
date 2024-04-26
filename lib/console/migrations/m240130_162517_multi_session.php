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
 * Class m240130_162517_multi_session
 */
class m240130_162517_multi_session extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'MULTI_SESSION_ENABLED',
            'configuration_title' => 'Multi-sessions are allowed?',
            'configuration_description' => 'Allow multiple active logins for same customer?',
            'configuration_group_id' => 'BOX_CONFIGURATION_SESSIONS',
            'configuration_value' => 'false',
            'sort_order' => '10',
            'set_function' => "tep_cfg_select_option(array('true', 'false'),",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeConfigurationKeys(['MULTI_SESSION_ENABLED']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240130_162517_multi_session cannot be reverted.\n";

        return false;
    }
    */
}
