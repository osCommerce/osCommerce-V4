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
 * Class m230816_142130_backend_search_settings_update
 */
class m230816_142130_backend_search_settings_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'BACKEND_SEARCH_SHOW_DATA'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_key' => 'BACKEND_SEARCH_SHOW_DATA',
                'configuration_title' => 'Show fields on search on add product in edit order ',
                'configuration_description' => 'Which fields of products should be shown on search',
                'configuration_group_id' => 'BOX_CONFIGURATION_WIDE_SEACH',
                'configuration_value' => 'Image, SKU, Price',
                'sort_order' => '11',
                'set_function' => 'tep_cfg_select_multioption(array(\'Image\', \'SKU\',\'Price\',\'Stock\'),',
                'date_added' => (new yii\db\Expression('now()'))
            ]);
        }

        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'BACKEND_SEARCH_ON_ALL_LANGUAGES'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_key' => 'BACKEND_SEARCH_ON_ALL_LANGUAGES',
                'configuration_title' => 'Search cross all languages',
                'configuration_description' => 'Search cross all languages (result showed on one active language)',
                'configuration_group_id' => 'BOX_CONFIGURATION_WIDE_SEACH',
                'configuration_value' => 'False',
                'sort_order' => '15',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),',
                'date_added' => (new yii\db\Expression('now()'))
            ]);
        }

        $this->execute("update configuration set set_function='tep_cfg_select_option(array(\'Standard\', \'Simplified\',\'List\'),' where configuration_key='BACKEND_SEARCH_AGREGATE_PRODUCT_DATA' and configuration_group_id='BOX_CONFIGURATION_WIDE_SEACH'");

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230816_142130_backend_search_settings_update cannot be reverted.\n";

        $this->execute("delete from configuration where configuration_key='BACKEND_SEARCH_SHOW_DATA' and configuration_group_id='BOX_CONFIGURATION_WIDE_SEACH'");
        $this->execute("delete from configuration where configuration_key='BACKEND_SEARCH_ON_ALL_LANGUAGES' and configuration_group_id='BOX_CONFIGURATION_WIDE_SEACH'");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230816_142130_backend_search_settings_update cannot be reverted.\n";

        return false;
    }
    */
}
