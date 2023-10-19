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
 * Class m230721_163716_groups_sort_order
 */
class m230721_163716_groups_sort_order extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('sort_order', 'groups')) {
            $this->addColumn('groups', 'sort_order', $this->integer(11)->notNull()->defaultValue(0));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->isFieldExists('sort_order', 'groups')) {
            $this->dropColumn('groups', 'sort_order');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230721_163716_groups_sort_order cannot be reverted.\n";

        return false;
    }
    */
}
