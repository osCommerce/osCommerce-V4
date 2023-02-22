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
 * Class m230129_211109_remove_cron_menu_acl
 */
class m230129_211109_remove_cron_menu_acl extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('CronScheduler')) {
                if (!\common\helpers\Extensions::isInstalled('CronScheduler')) {
                    $this->removeAdminMenu('BOX_HEADING_CRON_MANAGER');
                    $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CRON_MANAGER']);
                }
            }
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230129_211109_remove_cron_menu_acl cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230129_211109_remove_cron_menu_acl cannot be reverted.\n";

        return false;
    }
    */
}
