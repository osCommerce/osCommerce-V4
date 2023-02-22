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
 * Class m221214_080551_fix_usergroups_menu
 */
class m221214_080551_fix_usergroups_menu extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (class_exists('\common\helpers\Extensions')) {
            $row = \common\models\AdminBoxes::findOne(['title' => 'BOX_CUSTOMERS_GROUPS']);
            if (!empty($row)) {
                $row->config_check = '';
                $row->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221214_080551_fix_usergroups_menu cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221214_080551_fix_usergroups_menu cannot be reverted.\n";

        return false;
    }
    */
}
