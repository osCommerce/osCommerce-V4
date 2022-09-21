<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\Migration;

/**
 * Class m220628_170337_warehouses_products_layers
 */
class m220628_170337_warehouses_products_layers extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('warehouses_products_layers', true) === null) {
            $this->createTable('warehouses_products_layers', [
                'layers_id' => $this->primaryKey(),
                'layers_name' => $this->string(128)->notNull()->defaultValue(''),
                'expiry_date' => $this->date()->notNull()->defaultValue('0000-00-00'),
            ]);
            try {
                $this->createIndex('idx_expiry_date', 'warehouses_products_layers', ['expiry_date']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('layers_id', 'warehouses_products')) {
            $this->addColumn('warehouses_products', 'layers_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->dropPrimaryKey('', 'warehouses_products');
                $this->addPrimaryKey('', 'warehouses_products', ['warehouse_id', 'products_id', 'suppliers_id', 'location_id', 'layers_id']);
                $this->createIndex('idx_layers_id', 'warehouses_products', ['layers_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('layers_id', 'orders_products_allocate')) {
            $this->addColumn('orders_products_allocate', 'layers_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->dropPrimaryKey('', 'orders_products_allocate');
                $this->addPrimaryKey('', 'orders_products_allocate', ['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id', 'layers_id']);
                $this->createIndex('idx_layers_id', 'orders_products_allocate', ['layers_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('layers_id', 'temporary_stock')) {
            $this->addColumn('temporary_stock', 'layers_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->createIndex('idx_layers_id', 'temporary_stock', ['layers_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('layers_id', 'stock_history')) {
            $this->addColumn('stock_history', 'layers_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->createIndex('idx_layers_id', 'stock_history', ['layers_id']);
            } catch (\Exception $ex) {
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->db->getTableSchema('warehouses_products_layers', true) !== null) {
            $this->dropTable('warehouses_products_layers');
        }

        if ($this->isFieldExists('layers_id', 'warehouses_products')) {
            try {
                $this->dropPrimaryKey('', 'warehouses_products');
                $this->addPrimaryKey('', 'warehouses_products', ['warehouse_id', 'products_id', 'suppliers_id', 'location_id']);
            } catch (\Exception $ex) {
            }
            $this->dropColumn('warehouses_products', 'layers_id');
        }

        if ($this->isFieldExists('layers_id', 'orders_products_allocate')) {
            try {
                $this->dropPrimaryKey('', 'orders_products_allocate');
                $this->addPrimaryKey('', 'orders_products_allocate', ['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id']);
            } catch (\Exception $ex) {
            }
            $this->dropColumn('orders_products_allocate', 'layers_id');
        }

        if ($this->isFieldExists('layers_id', 'temporary_stock')) {
            $this->dropColumn('temporary_stock', 'layers_id');
        }

        if ($this->isFieldExists('layers_id', 'stock_history')) {
            $this->dropColumn('stock_history', 'layers_id');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220628_170337_warehouses_products_layers cannot be reverted.\n";

        return false;
    }
    */
}
