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
 * Class m221228_151125_account_history
 */
class m221228_151125_account_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('account', [
            'TEXT_ORDER_NUMBER_NEW' => 'Order',
            'TEXT_ORDER_HISTORY_TOTAL' => 'Total',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221228_151125_account_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221228_151125_account_history cannot be reverted.\n";

        return false;
    }
    */
}
