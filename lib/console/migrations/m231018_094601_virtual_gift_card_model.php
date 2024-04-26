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
 * Class m231018_094601_virtual_gift_card_model
 */
class m231018_094601_virtual_gift_card_model extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'VIRTUAL_GIFT_CARD_MODEL',
            'configuration_title' => 'Virtual Gift Card Model',
            'configuration_description' => 'Virtual Gift Card Model',
            'configuration_value' => 'VIRTUAL_GIFT_CARD',
            'configuration_group_id' => 'TEXT_LISTING_PRODUCTS',
            'sort_order' => '777',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231018_094601_virtual_gift_card_model cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231018_094601_virtual_gift_card_model cannot be reverted.\n";

        return false;
    }
    */
}
