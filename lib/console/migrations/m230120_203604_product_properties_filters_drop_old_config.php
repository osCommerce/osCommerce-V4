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
 * Class m230120_203604_product_properties_filters_drop_old_config
 */
class m230120_203604_product_properties_filters_drop_old_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension'))
        {
            if(!$this->isOldExtension('ProductPropertiesFilters'))
            {
                $this->removeConfigurationKeys(['DISPLAY_ONE_VALUE_FILTER', 'ALWAYS_SHOW_FILTERS_ON_BRAND_PAGE']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230120_203604_product_properties_filters_drop_old_config cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230120_203604_product_properties_filters_drop_old_config cannot be reverted.\n";

        return false;
    }
    */
}
