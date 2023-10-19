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
 * Class m230811_154825_groups_products_sets_discount
 */
class m230811_154825_groups_products_sets_discount extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('products_prices')) {
            if (!$this->isFieldExists('products_sets_discount', 'products_prices')) {
                $this->addColumn('products_prices', 'products_sets_discount', $this->decimal(12, 2)->notNull()->defaultValue(0));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230811_154825_groups_products_sets_discount cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230811_154825_groups_products_sets_discount cannot be reverted.\n";

        return false;
    }
    */
}
