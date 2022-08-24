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
 * Class m220817_172702_remove_order_journal_acl
 */
class m220817_172702_remove_order_journal_acl extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropAcl(['ACL_ORDER', 'IMAGE_JOURNAL']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220817_172702_remove_order_journal_acl cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220817_172702_remove_order_journal_acl cannot be reverted.\n";

        return false;
    }
    */
}
