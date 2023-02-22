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
 * Class m221227_181602_drop_old_connectors
 */
class m221227_181602_drop_old_connectors extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension'))
        {
            // Start WooLink43
            if(!$this->isOldExtension('Woolink43'))
            {
                // Removing tables if extension not installed
                if(!\common\helpers\Extensions::isInstalled('Woolink43')) {
                    $tables = [
                        'connector_woolink43_configuration',
                        'connector_woolink43_map_attribute',
                        'connector_woolink43_map_attribute_value',
                        'connector_woolink43_map_category',
                        'connector_woolink43_map_customer',
                        'connector_woolink43_map_inventory',
                        'connector_woolink43_map_manufacturer',
                        'connector_woolink43_map_order',
                        'connector_woolink43_map_order_product',
                        'connector_woolink43_map_product',
                        'connector_woolink43_map_property',
                        'connector_woolink43_map_property_value',
                    ];
                    $this->dropTables($tables);
                }
                // Removing unused confuguration key for WooLink43
                $this->removeConfigurationKeys('EXTENSION_CONNECTOR_WOOLINK43_ENABLED');
            }
            // End WooLink47

            // Start MageLink23
            if(!$this->isOldExtension('MageLink23'))
            {
                // Removing tables if extension not installed
                if(!\common\helpers\Extensions::isInstalled('MageLink23'))
                {
                    $mageTables = [
                        'connector_magelink23_configuration',
                        'connector_magelink23_map_manufacturer',
                        'connector_magelink23_map_category',
                        'connector_magelink23_map_attribute',
                        'connector_magelink23_map_attribute_value',
                        'connector_magelink23_map_property',
                        'connector_magelink23_map_property_value',
                        'connector_magelink23_map_customer',
                        'connector_magelink23_map_customer_address',
                        'connector_magelink23_map_order',
                        'connector_magelink23_map_order_product',
                        'connector_magelink23_map_product',
                        'connector_magelink23_map_tax_class',
                        'connector_magelink23_map_inventory',
                        'connector_magelink23_map_shipment'
                    ];
                    $this->dropTables($mageTables);
                }
            }
            // End MageLink23

            // Start PrestaLink17
            if(!$this->isOldExtension('PrestaLink17'))
            {
                // Removing tables if extension not installed
                if(!\common\helpers\Extensions::isInstalled('PrestaLink17'))
                {
                    $prestaTables = [
                        'connector_prestalink17_configuration',
                        'connector_prestalink17_map_customer',
                        'connector_prestalink17_map_customer_address',
                        'connector_prestalink17_map_order',
                        'connector_prestalink17_map_order_product'
                    ];
                    $this->dropTables($prestaTables);
                }
                // Removing unused configuration key
                $this->removeConfigurationKeys('EXTENSION_CONNECTOR_PRESTALINK17_ENABLED');
            }
            // End PrestaLink17
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221227_181602_drop_old_connectors cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221227_181602_drop_old_connectors cannot be reverted.\n";

        return false;
    }
    */
}
