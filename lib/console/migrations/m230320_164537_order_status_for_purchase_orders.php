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
 * Class m230320_164537_order_status_for_purchase_orders
 */
class m230320_164537_order_status_for_purchase_orders extends Migration
{
    const POES_PENDING = 5;
    const POES_PROCESSING = 15;
    const POES_RECEIVED = 25;
    const POES_DISPATCHED = 35;
    const POES_CANCELLED = 55;
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('PurchaseOrders'))
            {
                \common\models\OrdersStatus::updateAll(['order_evaluation_state_id' => self::POES_PENDING], 'orders_status_id = 33 AND orders_status_groups_id = 9');
                \common\models\OrdersStatus::updateAll(['order_evaluation_state_id' => self::POES_PROCESSING], 'orders_status_id = 34 AND orders_status_groups_id = 9');
                \common\models\OrdersStatus::updateAll(['order_evaluation_state_id' => self::POES_RECEIVED], 'orders_status_id = 35 AND orders_status_groups_id = 9');
                if (!\common\models\OrdersStatus::findOne('orders_status_groups_id = 9 AND order_evaluation_state_id = '.self::POES_CANCELLED))
                {
                    \common\models\OrdersStatus::insertNew(9, 'Canceled', ['order_evaluation_state_id' => self::POES_CANCELLED]);
                }
                if (!\common\models\OrdersStatus::findOne('orders_status_groups_id = 9 AND order_evaluation_state_id = '.self::POES_DISPATCHED))
                {
                    \common\models\OrdersStatus::insertNew(9, 'Completed', ['order_evaluation_state_id' => self::POES_DISPATCHED]);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230320_164537_order_status_for_purchase_orders cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230320_164537_order_status_for_purchase_orders cannot be reverted.\n";

        return false;
    }
    */
}
