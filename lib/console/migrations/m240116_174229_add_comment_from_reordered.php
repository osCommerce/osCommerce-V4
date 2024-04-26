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
 * Class m240116_174229_add_comment_from_reordered
 */
class m240116_174229_add_comment_from_reordered extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'ADD_COMMENT_FROM_REORDERED',
            'configuration_title' => 'Add Comments From Re-Ordered Order',
            'configuration_description' => 'Add comments from re-ordered order.',
            'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
            'configuration_value' => 'true',
            'sort_order' => '77777',
            'set_function' => "tep_cfg_select_option(array('true', 'false'),",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeConfigurationKeys(['ADD_COMMENT_FROM_REORDERED']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240116_174229_add_comment_from_reordered cannot be reverted.\n";

        return false;
    }
    */
}
