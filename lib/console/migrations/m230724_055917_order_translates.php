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
 * Class m230724_055917_order_translates
 */
class m230724_055917_order_translates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'PLATFORM_DETAILS' => 'Platform Details',
            'ORDER_STATUSES' => 'Order Statuses',
            'ADDRESSES_HAS_ERROR' => 'The %s addresses has an error, please %s it',
            'SELECT_EXISTING_CUSTOMERS' => 'Select from existing customers',
            'TYPE_FIND_CUSTOMER' => 'Type to find customer',
            'TEXT_REASSING' => 'Reassing',
            'TEXT_CHOOSE' => 'Choose',
            'OR_IF_YOUD_LIKE' => 'or, if you\'d like',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
