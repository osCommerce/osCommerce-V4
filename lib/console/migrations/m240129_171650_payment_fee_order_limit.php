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
 * Class m240129_171650_payment_fee_order_limit
 */
class m240129_171650_payment_fee_order_limit extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('ordertotal', [
            'TABLE_HEADING_ORDER_LIMIT' => 'Order Limit<br><small>(0 - no limit)</small>',
            'TEXT_MINIMUM_ORDER' => 'Min (from)',
            'TEXT_MAXIMUM_ORDER' => 'Max (up to)',
        ]);
        if (!$this->isFieldExists('minimum_order', 'payment_fee')) {
            $this->addColumn('payment_fee', 'minimum_order', $this->decimal(11, 2)->notNull()->defaultValue(0));
        }
        if (!$this->isFieldExists('maximum_order', 'payment_fee')) {
            $this->addColumn('payment_fee', 'maximum_order', $this->decimal(11, 2)->notNull()->defaultValue(0));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('ordertotal', [
           'TABLE_HEADING_ORDER_LIMIT',
           'TEXT_MINIMUM_ORDER',
           'TEXT_MAXIMUM_ORDER',
        ]);
        if ($this->isFieldExists('minimum_order', 'payment_fee')) {
            $this->dropColumn('payment_fee', 'minimum_order');
        }
        if ($this->isFieldExists('maximum_order', 'payment_fee')) {
            $this->dropColumn('payment_fee', 'maximum_order');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240129_171650_payment_fee_order_limit cannot be reverted.\n";

        return false;
    }
    */
}
