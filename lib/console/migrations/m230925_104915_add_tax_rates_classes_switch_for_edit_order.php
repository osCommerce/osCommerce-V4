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
 * Class m230925_104915_add_tax_rates_classes_switch_for_edit_order
 */
class m230925_104915_add_tax_rates_classes_switch_for_edit_order extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'BACKEND_TAX_RATES_OR_CLASSES',
            'configuration_title' => 'Tax selection',
            'configuration_description' => 'Source for tax selection',
            'configuration_group_id' => 'BOX_CONFIGURATION_EDIT_ORDER_SETTINGS',
            'configuration_value' => 'CLASSES',
            'sort_order' => '100',
            'set_function' => "tep_cfg_select_option(array('CLASSES', 'RATES'),",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230925_104915_add_tax_rates_classes_switch_for_edit_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230925_104915_add_tax_rates_classes_switch_for_edit_order cannot be reverted.\n";

        return false;
    }
    */
}
