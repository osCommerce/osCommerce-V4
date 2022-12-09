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
 * Class m221130_153146_drop_customer_code_config
 */
class m221130_153146_drop_customer_code_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys(['ACCOUNT_ERP_CUSTOMER_ID', 'ACCOUNT_ERP_CUSTOMER_CODE']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->batchInsertSafe('configuration', ['configuration_title', 'configuration_key', 'configuration_value', 'configuration_description', 'configuration_group_id', 'sort_order', 'last_modified', 'date_added', 'use_function', 'set_function'],[
            ['ERP Customer Id', 'ACCOUNT_ERP_CUSTOMER_ID', 'visible', 'Display ERP Customer Id in the customers account', 5, 100, NULL, new \yii\db\Expression('NOW()'), NULL, 'tep_cfg_select_option(array(\'disabled\', \'visible\', \'visible_register\', \'required\', \'required_register\'),'],
            ['ERP Customer Code', 'ACCOUNT_ERP_CUSTOMER_CODE', 'visible', 'Display ERP Customer Code in the customers account', 5, 100, NULL, new \yii\db\Expression('NOW()'), NULL, 'tep_cfg_select_option(array(\'disabled\', \'visible\', \'visible_register\', \'required\', \'required_register\'),'],
        ]);

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221130_153146_drop_customer_code_config cannot be reverted.\n";

        return false;
    }
    */
}
