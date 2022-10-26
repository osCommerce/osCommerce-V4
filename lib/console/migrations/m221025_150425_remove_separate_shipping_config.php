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
 * Class m221025_150425_remove_separate_shipping_config
 */
class m221025_150425_remove_separate_shipping_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys('SHIPPING_SEPARATELY');

        $this->removeTranslation('configuration', [
            'SHIPPING_SEPARATELY_DESC',
            'SHIPPING_SEPARATELY_TITLE',
            'PRODUCTS_BUNDLE_SETS_DESC',
            'PRODUCTS_BUNDLE_SETS_TITLE',
            'CUSTOMERS_GROUPS_ENABLE_DESC',
            'CUSTOMERS_GROUPS_ENABLE_TITLE',
            'SALES_STATS_DISPLAY_DESC',
            'SALES_STATS_DISPLAY_TITLE'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221025_150425_remove_separate_shipping_config cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221025_150425_remove_separate_shipping_config cannot be reverted.\n";

        return false;
    }
    */
}
