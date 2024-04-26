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
 * Class m231226_120625_remove_product_price_free
 */
class m231226_120625_remove_product_price_free extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys(['PRODUCT_PRICE_FREE']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'PRODUCT_PRICE_FREE',
            'configuration_title' => 'Product zero price',
            'configuration_description' => 'Show word Free instead of zero price.',
            'configuration_group_id' => 'BOX_CONFIGURATION_SHIPPING_PACKAGING',
            'configuration_value' => 'false',
            'sort_order' => '0',
            'set_function' => "tep_cfg_select_option(array('true', 'false'),",
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231226_120625_remove_product_price_free cannot be reverted.\n";

        return false;
    }
    */
}
