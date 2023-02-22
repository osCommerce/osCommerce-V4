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
 * Class m230109_102127_max_and_minimum_order_qty
 */
class m230109_102127_max_and_minimum_order_qty extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('MaxOrderQty'))
            {
                $this->removeTranslation('admin/categories', ['TEXT_PRODUCTS_ORDER_QUANTITY_MAX']);
            }

            if (!$this->isOldExtension('MinimumOrderQty'))
            {
                $this->removeTranslation('admin/categories',[
                    'TEXT_PRODUCTS_ORDER_QUANTITY_MINIMAL',
                    'TEXT_PRODUCTS_ORDER_QUANTITY_MAX',
                ]);
                $this->removeTranslation('main',[
                    'TEXT_MIN_QTY_TO_PURCHASE',
                    'TEXT_QTY_STEP_TO_PURCHASE',
                    'TEXT_MAX_QTY_TO_PURCHASE'
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230109_102127_max_and_minimum_order_qty cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230109_102127_max_and_minimum_order_qty cannot be reverted.\n";

        return false;
    }
    */
}
