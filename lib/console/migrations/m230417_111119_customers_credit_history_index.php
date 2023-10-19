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
 * Class m230417_111119_customers_credit_history_index
 */
class m230417_111119_customers_credit_history_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('customers_credit_history_customers_id_idx', 'customers_credit_history', ['customers_id', 'credit_type']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230417_111119_customers_credit_history_index cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230417_111119_customers_credit_history_index cannot be reverted.\n";

        return false;
    }
    */
}
