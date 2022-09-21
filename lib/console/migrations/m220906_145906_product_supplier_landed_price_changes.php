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
 * Class m220906_145906_product_supplier_landed_price_changes
 */
class m220906_145906_product_supplier_landed_price_changes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // remove NOT NULL and default value
        $this->alterColumn('suppliers_products', 'landed_price', 'decimal(11,2)');
        $this->update('suppliers_products', ['landed_price' => null], 'landed_price = 0');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220906_145906_product_supplier_landed_price_changes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220906_145906_product_supplier_landed_price_changes cannot be reverted.\n";

        return false;
    }
    */
}
