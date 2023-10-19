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
 * Class m230815_104408_coupon_updates
 */
class m230815_104408_coupon_updates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('coupons')) {
            if (!$this->isFieldExists('coupon_groups', 'coupons')) {
                $this->addColumn('coupons', 'coupon_groups', $this->string(2048)->notNull()->defaultValue(''));
            }
            if (!$this->isFieldExists('restrict_to_manufacturers', 'coupons')) {
                $this->addColumn('coupons', 'restrict_to_manufacturers', $this->text()->notNull()->defaultValue(''));
            }
            $this->addTranslation('admin/coupon_admin', [
                'COUPON_GROUPS' => 'Valid for Customer Groups',
                'COUPON_GROUPS_HELP' => 'Please select customer groups the coupon will be valid for.',
                'COUPON_MANUFACTURERS' => 'Valid Manufacturers List',
                'COUPON_MANUFACTURERS_HELP' => 'A comma separated list of Manufacturers that this coupon can be used with, leave blank for no restrictions.',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230815_104408_coupon_updates cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230815_104408_coupon_updates cannot be reverted.\n";

        return false;
    }
    */
}
