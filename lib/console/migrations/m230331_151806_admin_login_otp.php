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
 * Class m230331_151806_admin_login_otp
 */
class m230331_151806_admin_login_otp extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('configuration',[
            'configuration_title' => 'Enable OTP admin login',
            'configuration_key' => 'ADMIN_LOGIN_OTP_ENABLE',
            'configuration_value' => 'False',
            'configuration_description' => 'Enable a one-time password to log into admin area. Admin Members will no longer have static passwords.',
            'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}