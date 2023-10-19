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
 * Class m230712_145145_delete_config_key
 */
class m230712_145145_delete_config_key extends Migration
{
    /**`
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldProject')) {
            if (!$this->isOldProject()) {
                $this->removeConfigurationKeys('SEARCH_BY_ELEMENTS');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'SEARCH_BY_ELEMENTS',
            'configuration_value' => 'Name, Description, Manufacturer, SKU, EAN, UPC, Categories, Attributes, Properties',
            'set_function' => "cfgMultiSortable(array('Name', 'SKU','ASIN', 'EAN', 'ISBN','UPC', 'Description', 'Categories', 'Attributes', 'Properties', 'Manufacturer'),",
            'configuration_group_id' => 'BOX_CONFIGURATION_WIDE_SEACH',
            'configuration_title' => 'Products properties to search (plain product description ext.)',
            'configuration_description' => 'Products fields which will be used in search and their priority. Available with plain product description extension.',
        ]);

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230712_145145_delete_config_key cannot be reverted.\n";

        return false;
    }
    */
}
