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
 * Class m230608_121800_order_quantity_step_delete_old_translation
 */
class m230608_121800_order_quantity_step_delete_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('OrderQuantityStep'))
            {
                $this->removeTranslation('admin/main', [
                    'TEXT_PRODUCTS_ITEM_IN_VIRTUAL_ITEM_QTY', 
                    'TEXT_PRODUCTS_ITEM_IN_VIRTUAL_ITEM_STEP',
                ]);
                $this->removeTranslation('admin/categories', ['TEXT_PRODUCTS_ORDER_QUANTITY_STEP']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/main', [
            'TEXT_PRODUCTS_ITEM_IN_VIRTUAL_ITEM_QTY' => 'Items to Virtual item',
            'TEXT_PRODUCTS_ITEM_IN_VIRTUAL_ITEM_STEP' => 'Virtual item steps (in Items)'
        ]);
        $this->addTranslation('admin/categories', [
            'TEXT_PRODUCTS_ORDER_QUANTITY_STEP' => 'Order quantity step',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230608_121800_order_quantity_step_delete_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
