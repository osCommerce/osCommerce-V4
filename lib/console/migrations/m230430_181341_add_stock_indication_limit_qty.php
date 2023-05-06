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
 * Class m230430_181341_add_stock_indication_limit_qty
 */
class m230430_181341_add_stock_indication_limit_qty extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/stock-indication', ['TEXT_LIMIT_CART_QTY_BY_STOCK' => 'Limit max count in cart by stock qty']);
        $this->addColumnIfMissing('products_stock_indication', 'limit_cart_qty_by_stock', $this->tinyInteger(1)->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230430_181341_add_stock_indication_limit_qty cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230430_181341_add_stock_indication_limit_qty cannot be reverted.\n";

        return false;
    }
    */
}
