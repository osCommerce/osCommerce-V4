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
 * Class m230116_133307_coupon_free_shipping_update
 */
class m230116_133307_coupon_free_shipping_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('free_shipping','coupons')) {
            $this->addColumn(
                'coupons',
                'free_shipping',
                $this->integer(1)->notNull()->defaultValue(0)->after('coupon_type')
            );
            $this->getDb()->createCommand(
                "UPDATE coupons SET free_shipping=1, coupon_type='F' WHERE coupon_type='S'"
            )->execute();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230116_133307_coupon_free_shipping_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230116_133307_coupon_free_shipping_update cannot be reverted.\n";

        return false;
    }
    */
}
