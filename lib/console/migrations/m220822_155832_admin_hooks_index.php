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
 * Class m220822_155832_admin_hooks_index
 */
class m220822_155832_admin_hooks_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('idx_uniq', 'hooks', ['page_name', 'page_area', 'sort_order', 'extension_name'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220822_155832_admin_hooks_index cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220822_155832_admin_hooks_index cannot be reverted.\n";

        return false;
    }
    */
}
