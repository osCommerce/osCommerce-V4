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
 * Class m231220_152142_order_customer_billing_email
 */
class m231220_152142_order_customer_billing_email extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isFieldExists('billing_email_address', 'orders')) {
            $this->alterColumn('orders', 'billing_email_address', $this->string(96)->notNull()->defaultValue(''));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}