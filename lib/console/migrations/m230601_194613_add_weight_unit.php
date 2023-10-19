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
 * Class m230601_194613_add_weight_unit
 */
class m230601_194613_add_weight_unit extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', [
            'TEXT_WEIGHT_UNIT_KG'   => 'Kgs',
            'TEXT_WEIGHT_UNIT_LB'   => 'Lbs',
        ]);
        $this->addTranslation('admin/main', [
            'TEXT_WEIGHT_UNIT_KG'   => 'Kgs',
            'TEXT_WEIGHT_UNIT_LB'   => 'Lbs',
        ]);
        $this->addTranslation('admin/design', [
            'TEXT_DISPLAY_WEIGHT'   => 'Display weight',
        ]);
        $this->addConfigurationKey([
            'configuration_key' => 'WEIGHT_UNIT_DEFAULT',
            'configuration_title' => 'Default weight unit',
            'configuration_description' => 'Default weight unit',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'configuration_value' => 'KG',
            'sort_order' => '100',
            'set_function' => "tep_cfg_select_option(array('KG', 'LB'),",
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230601_194613_add_weight_unit cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230601_194613_add_weight_unit cannot be reverted.\n";

        return false;
    }
    */
}
