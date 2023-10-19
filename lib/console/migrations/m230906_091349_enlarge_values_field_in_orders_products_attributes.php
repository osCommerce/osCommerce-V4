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
 * Class m230906_091349_enlarge_values_field_in_orders_products_attributes
 */
class m230906_091349_enlarge_values_field_in_orders_products_attributes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->db->createCommand("ALTER TABLE orders_products_attributes MODIFY products_options_values VARCHAR(255) NOT NULL DEFAULT '';")->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230906_091349_enlarge_values_field_in_orders_products_attributes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230906_091349_enlarge_values_field_in_orders_products_attributes cannot be reverted.\n";

        return false;
    }
    */
}
