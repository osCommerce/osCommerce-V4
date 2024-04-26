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
 * Class m240206_110508_max_show_attribute_length
 */
class m240206_110508_max_show_attribute_length extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'MAX_SHOW_ATTRIBUTE_LENGTH',
            'configuration_title' => 'Maximum Attribute Length to Show',
            'configuration_description' => 'Maximum attribute length to show on order page.',
            'configuration_group_id' => 'BOX_CONFIGURATION_MAXIMUM_VALUES',
            'configuration_value' => '20',
            'sort_order' => '777',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeConfigurationKeys(['MAX_SHOW_ATTRIBUTE_LENGTH']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240206_110508_max_show_attribute_length cannot be reverted.\n";

        return false;
    }
    */
}
