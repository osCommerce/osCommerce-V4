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
 * Class m230530_114523_remove_maintenance
 */
class m230530_114523_remove_maintenance extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $existOldExt = ($ext = \common\helpers\Extensions::isAllowed('Maintenance')) && !method_exists($ext, 'getUserFile');
        if (!$this->isOldProject() && !$existOldExt) {
            $this->removeTranslation('main', [
                'TEXT_ADMIN_DOWN_FOR_MAINTENANCE',
                'TEXT_BEFORE_DOWN_FOR_MAINTENANCE',
                'WARNING_DOWN_FOR_MAINTENANCE',
            ]);
            $this->removeTranslation('admin/main', ['BOX_CONFIGURATION_MAINTENANCE']);
            $this->removeTranslation('configuration', [
                'DOWN_FOR_MAINTENANCE_ALLOWED_IP_DESC',
                'DOWN_FOR_MAINTENANCE_ALLOWED_IP_TITLE',
                'DOWN_FOR_MAINTENANCE_DESC',
                'DOWN_FOR_MAINTENANCE_FILENAME_DESC',
                'DOWN_FOR_MAINTENANCE_FILENAME_TITLE',
                'DOWN_FOR_MAINTENANCE_PRICES_OFF_DESC',
                'DOWN_FOR_MAINTENANCE_PRICES_OFF_TITLE',
                'DOWN_FOR_MAINTENANCE_STATUS_DESC',
                'DOWN_FOR_MAINTENANCE_STATUS_TITLE',
                'DOWN_FOR_MAINTENANCE_TITLE',
                'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE_DESC',
                'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE_TITLE',
                'PERIOD_BEFORE_DOWN_FOR_MAINTENANCE_DESC',
                'PERIOD_BEFORE_DOWN_FOR_MAINTENANCE_TITLE',
                'WARN_BEFORE_DOWN_FOR_MAINTENANCE_DESC',
                'WARN_BEFORE_DOWN_FOR_MAINTENANCE_TITLE',
            ]);
            $this->removeTranslation('maintenance');
            $this->removeConfigurationKeys([
                'DOWN_FOR_MAINTENANCE',
                'DOWN_FOR_MAINTENANCE_FILENAME',
                'DOWN_FOR_MAINTENANCE_PRICES_OFF',
                'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE',
                'WARN_BEFORE_DOWN_FOR_MAINTENANCE',
                'PERIOD_BEFORE_DOWN_FOR_MAINTENANCE',
                'DOWN_FOR_MAINTENANCE_STATUS',
                'DOWN_FOR_MAINTENANCE_ALLOWED_IP',
            ]);
            if ($this->isTableExists('configuration_group')) {
                $this->getDb()->createCommand("DELETE FROM `configuration_group` WHERE `configuration_group_title` = 'Site Maintenance'")->execute();
            }
            $this->removeConfigurationKeys([
                'Maintenance_EXTENSION_PLATFORM',
                'Maintenance_EXTENSION_ALLOWED_IP',
            ]);
            $this->dropAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_CONFIGURATION_MAINTENANCE']);
            $this->removeAdminMenu('BOX_CONFIGURATION_MAINTENANCE');
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230530_114523_remove_maintenance cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230530_114523_remove_maintenance cannot be reverted.\n";

        return false;
    }
    */
}
