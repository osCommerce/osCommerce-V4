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
 * Class m231218_151517_subscribers_delete_old_config
 */
class m231218_151517_subscribers_delete_old_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys(['ENABLE_CUSTOMERS_NEWSLETTER', 'SUBSCRIBERS_DOUBLE_OPT_IN']);
        $this->removePlatformConfigurationKeys(['ENABLE_CUSTOMERS_NEWSLETTER', 'SUBSCRIBERS_DOUBLE_OPT_IN']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $check = (new \yii\db\Query())->from('configuration')->where(['configuration_key' => 'ENABLE_CUSTOMERS_NEWSLETTER'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'Enable Subscribe to newsletter',
                'configuration_key' => 'ENABLE_CUSTOMERS_NEWSLETTER',
                'configuration_value' => 'true',
                'configuration_description' => 'Subscribe to regular offers',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
                'sort_order' => 1001,
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'),',
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }

        $check = (new \yii\db\Query())->from('configuration')->where(['configuration_key' => 'SUBSCRIBERS_DOUBLE_OPT_IN'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'Subscribers Double Opt-in',
                'configuration_key' => 'SUBSCRIBERS_DOUBLE_OPT_IN',
                'configuration_value' => 'True',
                'configuration_description' => 'Send confirmation email before saving subsriber',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
                'sort_order' => 1002,
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),',
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231218_151517_subscribers_delete_old_config cannot be reverted.\n";

        return false;
    }
    */
}
