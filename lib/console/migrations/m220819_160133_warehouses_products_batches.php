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
 * Class m220819_160133_warehouses_products_batches
 */
class m220819_160133_warehouses_products_batches extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
            'TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME' => 'Batch:'
        ]);

        if ($this->db->getTableSchema('warehouses_products_batches', true) === null) {
            $this->createTable('warehouses_products_batches', [
                'batch_id' => $this->primaryKey(),
                'batch_name' => $this->string(128)->notNull()->defaultValue(''),
            ]);
            try {
                $this->createIndex('idx_batch_name', 'warehouses_products_batches', ['batch_name']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('batch_id', 'warehouses_products')) {
            $this->addColumn('warehouses_products', 'batch_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->dropPrimaryKey('', 'warehouses_products');
                $this->addPrimaryKey('', 'warehouses_products', ['warehouse_id', 'products_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id']);
                $this->createIndex('idx_batch_id', 'warehouses_products', ['batch_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('batch_id', 'orders_products_allocate')) {
            $this->addColumn('orders_products_allocate', 'batch_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->dropPrimaryKey('', 'orders_products_allocate');
                $this->addPrimaryKey('', 'orders_products_allocate', ['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id']);
                $this->createIndex('idx_batch_id', 'orders_products_allocate', ['batch_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('batch_id', 'temporary_stock')) {
            $this->addColumn('temporary_stock', 'batch_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->createIndex('idx_batch_id', 'temporary_stock', ['batch_id']);
            } catch (\Exception $ex) {
            }
        }

        if (!$this->isFieldExists('batch_id', 'stock_history')) {
            $this->addColumn('stock_history', 'batch_id', $this->integer(11)->notNull()->defaultValue(0));
            try {
                $this->createIndex('idx_batch_id', 'stock_history', ['batch_id']);
            } catch (\Exception $ex) {
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/main', [
            'TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME'
        ]);

        if ($this->db->getTableSchema('warehouses_products_batches', true) !== null) {
            $this->dropTable('warehouses_products_batches');
        }

        if ($this->isFieldExists('batch_id', 'warehouses_products')) {
            try {
                $this->dropPrimaryKey('', 'warehouses_products');
                $this->addPrimaryKey('', 'warehouses_products', ['warehouse_id', 'products_id', 'suppliers_id', 'location_id', 'layers_id']);
            } catch (\Exception $ex) {
            }
            $this->dropColumn('warehouses_products', 'batch_id');
        }

        if ($this->isFieldExists('batch_id', 'orders_products_allocate')) {
            try {
                $this->dropPrimaryKey('', 'orders_products_allocate');
                $this->addPrimaryKey('', 'orders_products_allocate', ['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id', 'layers_id']);
            } catch (\Exception $ex) {
            }
            $this->dropColumn('orders_products_allocate', 'batch_id');
        }

        if ($this->isFieldExists('batch_id', 'temporary_stock')) {
            $this->dropColumn('temporary_stock', 'batch_id');
        }

        if ($this->isFieldExists('batch_id', 'stock_history')) {
            $this->dropColumn('stock_history', 'batch_id');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220819_160133_warehouses_products_batches cannot be reverted.\n";

        return false;
    }
    */
}
