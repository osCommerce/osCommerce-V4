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
 * Class m230302_073446_remove_banner_group2
 */
class m230302_073446_remove_banner_group2 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeAdminMenu('BOX_BANNER_GROUPS');
        $this->removeAcl(['TEXT_SETTINGS', 'BOX_BANNER_GROUPS']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230302_073446_remove_banner_group2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230302_073446_remove_banner_group2 cannot be reverted.\n";

        return false;
    }
    */
}
