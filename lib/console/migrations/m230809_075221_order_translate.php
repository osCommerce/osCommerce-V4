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
 * Class m230809_075221_order_translate
 */
class m230809_075221_order_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'CUSTOMER_DATA' => 'Customer data',
            'ASSIGN_CUSTOMER' => 'Assign customer',
            'PAYMENT_IS_NOT_SELECTED' => 'Payment is not selected',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
