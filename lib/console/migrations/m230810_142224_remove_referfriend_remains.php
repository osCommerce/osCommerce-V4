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
 * Class m230810_142224_remove_referfriend_remains
 */
class m230810_142224_remove_referfriend_remains extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('ReferFriends')) {
                $this->removeConfigurationKeys(['REFER_FRIEND_GIFT_AMOUNT', 'REFER_FRIEND_COUPON_AMOUNT', 'REFER_FRIEND_SCHEME_VERSION', 'REFER_FRIEND_RELEASE_ORDER_STATUSES']);
                $this->removeAdminMenu(['parent' => 'BOX_HEADING_CONFIGURATION', 'title' => 'BOX_CONFIGURATION_REFER_FRIEND_SETTINGS']);
                $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_CONFIGURATION_REFER_FRIEND_SETTINGS']);
            }
            $this->removeAdminMenu(['parent' => 'BOX_HEADING_CONFIGURATION', 'title' => 'BOX_CONFIGURATION_SMS_UPDATES_CONFIGURATION']);
            $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_CONFIGURATION_SMS_UPDATES_CONFIGURATION']);

            $this->removeAdminMenu(['parent' => 'BOX_HEADING_CONFIGURATION', 'title' => 'BOX_CONFIGURATION_EXACT']);
            $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_CONFIGURATION_EXACT']);

            $this->removeAdminMenu(['parent' => 'BOX_HEADING_CONFIGURATION', 'title' => 'BOX_CONFIGURATION_SUPPORT']);
            $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_CONFIGURATION_SUPPORT']);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230810_142224_remove_referfriend_remains cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230810_142224_remove_referfriend_remains cannot be reverted.\n";

        return false;
    }
    */
}
