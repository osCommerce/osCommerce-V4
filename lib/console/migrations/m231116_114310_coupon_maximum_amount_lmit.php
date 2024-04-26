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
 * Class m231116_114310_coupon_maximum_amount_lmit
 */
class m231116_114310_coupon_maximum_amount_lmit extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
            if (!$this->isFieldExists('coupon_amount_maximum', 'coupons')) {
                $this->addColumn('coupons', 'coupon_amount_maximum', $this->decimal(15,2)->notNull());
                $this->addTranslation('admin/coupon_admin', [
                    'COUPON_AMOUNT_MAXIMUM' => 'Coupon Amount Maximum',
                    'COUPON_AMOUNT_MAXIMUM_HELP' => 'The maximum coupon amount if discount is bigger',
                ]);
            }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231116_114310_coupon_maximum_amount_lmit cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231116_114310_coupon_maximum_amount_lmit cannot be reverted.\n";

        return false;
    }
    */
}
