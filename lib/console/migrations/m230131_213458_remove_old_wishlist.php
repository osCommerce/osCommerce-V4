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
 * Class m230131_213458_remove_old_wishlist
 */
class m230131_213458_remove_old_wishlist extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldProject')) {
            if (!$this->isOldProject()) {
                $this->dropTables(['customers_wishlist', 'customers_wishlist_attributes']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230131_213458_remove_old_wishlist cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230131_213458_remove_old_wishlist cannot be reverted.\n";

        return false;
    }
    */
}
