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
 * Class m230309_105432_design_table_row
 */
class m230309_105432_design_table_row extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'ADD_COLUMN' => 'Add column',
            'REMOVE_COLUMN' => 'Remove column',
            'ORDERS_LIST_ITEMS' => 'Orders List Items',
            'BATCH_CHECKBOX_CELL' => 'Batch Checkbox cell',
            'ORDER_MARKERS_CELL' => 'Order Markers cell',
            'CUSTOMER_COLUMN_CELL' => 'Customer Column cell',
            'ORDER_TOTALS_CELL' => 'Order Totals cell',
            'ORDER_DESCRIPTION_CELL' => 'Order Description cell',
            'ORDER_PURCHASE_CELL' => 'Order Purchase cell',
            'ORDER_STATUS_CELL' => 'Order Status cell',
            'NEIGHBOUR_CELL' => 'Neighbour cell',
            'CUSTOMER_GENDER' => 'Customer Gender',
            'ORDER_LOCATION' => 'Order Location',
            'WALKIN_ORDER' => 'Walkin Order',
        ]);
        $this->addTranslation('admin/main',[
            'ORDERS_LIST' => 'Orders list',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
