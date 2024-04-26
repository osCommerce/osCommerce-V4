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
 * Class m240222_175630_show_order_stats
 */
class m240222_175630_show_order_stats extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'TEXT_SHOW_ORDER_STATS' => 'Show Statistics',
            'TEXT_ORDER_STATS_PRODUCTS' => 'Products:',
            'TEXT_ORDER_STATS_TOTAL' => 'Total:',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m240222_175630_show_order_stats cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240222_175630_show_order_stats cannot be reverted.\n";

        return false;
    }
    */
}
