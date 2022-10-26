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
 * Class m221013_160540_autologoff_on_checkout_success
 */
class m221013_160540_autologoff_on_checkout_success extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'AUTO_LOGOFF_GUEST_ON_SUCCESS'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'Force logoff GUEST customer after successful checkout',
                'configuration_key' => 'AUTO_LOGOFF_GUEST_ON_SUCCESS',
                'configuration_value' => 'False',
                'configuration_description' => 'Force logoff GUEST customer after successful checkout ',
                'configuration_group_id' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
                'sort_order' => 10001,
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),',
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m221013_160540_autologoff_on_checkout_success cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221013_160540_autologoff_on_checkout_success cannot be reverted.\n";

        return false;
    }
    */
}
