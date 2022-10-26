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
 * Class m221017_132017_admin_token_date
 */
class m221017_132017_admin_token_date extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumnIfMissing('admin', 'token_date', $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00')->after('token'));
        if (!(\common\models\Configuration::findOne(['configuration_key' => 'FORGOTTEN_PASSWORD_TOKEN_EXPIRE_MIN']) instanceof \common\models\Configuration)) {
            $this->insert('configuration', [
                'configuration_title' => 'Password recovery token expiration time (minutes)',
                'configuration_key' => 'FORGOTTEN_PASSWORD_TOKEN_EXPIRE_MIN',
                'configuration_value' => '5',
                'configuration_description' => 'Forgotten administrator password recovery token expiration time (minutes)',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
                'sort_order' => 100,
                'date_added' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}