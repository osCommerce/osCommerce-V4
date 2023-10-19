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
 * Class m230811_085653_search_settings_update
 */
class m230811_085653_search_settings_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        
       $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'BACKEND_MSEARCH_WORD_LENGTH'])->exists();
       if (!$check) {
           $this->insert('configuration', [
               'configuration_key' => 'BACKEND_MSEARCH_WORD_LENGTH',
               'configuration_title' => 'Backend minimum word length',
               'configuration_description' => 'Minimal length of the word for backend, that will be included in search',
               'configuration_group_id' => 'BOX_CONFIGURATION_WIDE_SEACH',
               'configuration_value' => '2',
               'sort_order' => '10',
               'date_added' => (new yii\db\Expression('now()'))
           ]);
       }

       $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'BACKEND_SEARCH_AGREGATE_PRODUCT_DATA'])->exists();
       if (!$check) {
           $this->insert('configuration', [
               'configuration_key' => 'BACKEND_SEARCH_AGREGATE_PRODUCT_DATA',
               'configuration_title' => 'Backend show product data',
               'configuration_description' => 'Which data additionaly showed on found product(related on search speed)',
               'configuration_group_id' => 'BOX_CONFIGURATION_WIDE_SEACH',
               'configuration_value' => 'Standard',
               'sort_order' => '15',
               'set_function' => 'tep_cfg_select_option(array(\'Standard\', \'Simplified\',\'List\',\'Name Only\'),',
               'date_added' => (new yii\db\Expression('now()'))
           ]);
       }


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230811_085653_search_settings_update cannot be reverted.\n";

       $this->execute("delete from configuration where configuration_key='BACKEND_MSEARCH_WORD_LENGTH' and configuration_group_id='BOX_CONFIGURATION_WIDE_SEACH'");
       $this->execute("delete from configuration where configuration_key='BACKEND_SEARCH_AGREGATE_PRODUCT_DATA' and configuration_group_id='BOX_CONFIGURATION_WIDE_SEACH'");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230811_085653_search_settings_update cannot be reverted.\n";

        return false;
    }
    */
}
