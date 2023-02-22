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
 * Class m230215_132613_user_groups_restrictions_stock_available
 */
class m230215_132613_user_groups_restrictions_stock_available extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('groups_products') && !$this->isFieldExists('stock_available', 'groups_products')) {
            $this->addColumn('groups_products', 'stock_available', $this->tinyInteger(1)->notNull()->defaultValue(1));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230215_132613_user_groups_restrictions_stock_available cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230215_132613_user_groups_restrictions_stock_available cannot be reverted.\n";

        return false;
    }
    */
}
