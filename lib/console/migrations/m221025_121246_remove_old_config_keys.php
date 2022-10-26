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
 * Class m221025_121246_remove_old_config_keys
 */
class m221025_121246_remove_old_config_keys extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys(['CUSTOMERS_GROUPS_ENABLE', 'PRODUCTS_BUNDLE_SETS', 'SALES_STATS_DISPLAY']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221025_121246_remove_old_config_keys cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221025_121246_remove_old_config_keys cannot be reverted.\n";

        return false;
    }
    */
}
