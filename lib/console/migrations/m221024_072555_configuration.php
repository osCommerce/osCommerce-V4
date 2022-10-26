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
 * Class m221024_072555_configuration
 */
class m221024_072555_configuration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('configuration', [
            'configuration_value' => 'disabled',
        ], [
            'configuration_key' => 'ACCOUNT_LANDLINE',
        ]);
        $this->update('configuration', [
            'configuration_value' => '12',
        ], [
            'configuration_key' => 'ADMIN_PASSWORD_MIN_LENGTH',
        ]);
        $this->update('configuration', [
            'configuration_value' => 'ULN',
        ], [
            'configuration_key' => 'ADMIN_PASSWORD_STRONG',
        ]);
        $this->update('configuration', [
            'configuration_value' => '12',
        ], [
            'configuration_key' => 'ENTRY_PASSWORD_MIN_LENGTH',
        ]);
        $this->update('configuration', [
            'configuration_value' => 'ULN',
        ], [
            'configuration_key' => 'PASSWORD_STRONG_REQUIRED',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221024_072555_configuration cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221024_072555_configuration cannot be reverted.\n";

        return false;
    }
    */
}
