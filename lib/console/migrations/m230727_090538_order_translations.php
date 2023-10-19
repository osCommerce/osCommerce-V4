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
 * Class m230727_090538_order_translations
 */
class m230727_090538_order_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders',[
            'ORDER_SUMMARY' => 'Order summary',
            'SAME_AS_SHIPPING_ADDRESS' => 'same as Shipping Address',
            'SHIPPING_IS_NOT_SELECTED' => 'Shipping is not selected',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
