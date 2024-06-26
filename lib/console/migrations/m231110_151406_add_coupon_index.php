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
 * Class m231110_151406_add_coupon_index
 */
class m231110_151406_add_coupon_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('idx_coupon_active', 'coupons', 'coupon_active');
        $this->createIndex('idx_coupon_id', 'coupon_redeem_track', 'coupon_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231110_151406_add_coupon_index cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231110_151406_add_coupon_index cannot be reverted.\n";

        return false;
    }
    */
}
