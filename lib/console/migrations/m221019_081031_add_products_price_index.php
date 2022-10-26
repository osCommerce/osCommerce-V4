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
 * Class m221019_081031_add_products_price_index
 */
class m221019_081031_add_products_price_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('idx_productd_group_price', 'products_prices', 'products_group_price');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221019_081031_add_products_price_index cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221019_081031_add_products_price_index cannot be reverted.\n";

        return false;
    }
    */
}
