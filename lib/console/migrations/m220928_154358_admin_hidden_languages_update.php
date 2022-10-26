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
 * Class m220928_154358_admin_hidden_languages_update
 */
class m220928_154358_admin_hidden_languages_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'FORCE_HIDE_ADMIN_LANGUAGE'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => 'Force hide languages in admin',
                'configuration_key' => 'FORCE_HIDE_ADMIN_LANGUAGE',
                'configuration_value' => 'True',
                'configuration_description' => 'Force hide languages marked as hidden in admin',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
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
//        echo "m220928_154358_admin_hidden_languages_update cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220928_154358_admin_hidden_languages_update cannot be reverted.\n";

        return false;
    }
    */
}
