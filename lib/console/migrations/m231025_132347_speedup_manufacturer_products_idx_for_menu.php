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
 * Class m231025_132347_speedup_manufacturer_products_idx_for_menu
 */
class m231025_132347_speedup_manufacturer_products_idx_for_menu extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('idx_manufacturers_pproducts_stockindication_id', 'products', ['manufacturers_id', 'products_id', 'stock_indication_id']);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m231025_132347_speedup_manufacturer_products_idx_for_menu cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231025_132347_speedup_manufacturer_products_idx_for_menu cannot be reverted.\n";

        return false;
    }
    */
}
