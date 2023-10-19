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
 * Class m230629_173816_warehouse_priority_delete_old_translation
 */
class m230629_173816_warehouse_priority_delete_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('WarehousePriority'))
            {
                $this->removeTranslation('admin/warehouse-priority');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/warehouse-priority', [
            'TEXT_WEIGHT_COEFFICIENT' => 'Weight Coefficient',
            'WAREHOUSE_PRIORITY_ORDERED_QUANTITY_NOTE' => '',
            'WAREHOUSE_PRIORITY_ORDERED_QUANTITY_PARAM' => 'Covered quantity (%)',
            'WAREHOUSE_PRIORITY_ORDERED_QUANTITY_TITLE'	=> 'Ordered quantity coverage',
            'WAREHOUSE_PRIORITY_PLATFORM_DISTANCE_NOTE' => '',
            'WAREHOUSE_PRIORITY_PLATFORM_DISTANCE_PARAM' => 'Distance (%)',
            'WAREHOUSE_PRIORITY_PLATFORM_DISTANCE_TITLE' =>	'Distance to Warehouse',
            'WAREHOUSE_PRIORITY_WAREHOUSE_STOCK_NOTE' => '',
            'WAREHOUSE_PRIORITY_WAREHOUSE_STOCK_PARAM' => 'Stock level',
            'WAREHOUSE_PRIORITY_WAREHOUSE_STOCK_TITLE' => 'Warehouse Stock',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230629_173816_warehouse_priority_delete_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
