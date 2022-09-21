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
 * Class m220829_134709_product_stock_popup_translation
 */
class m220829_134709_product_stock_popup_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/categories', [
            'TEXT_BACKEND_TEMPORARY_STOCK_DELETE_CONFIRM' => 'Do you really wish to release Back-end temporary stock allocation?',
            'TEXT_BACKEND_ALLOCATED_STOCK' => 'Back-end allocated stock',
            'TEXT_LOCATION' => 'Location',
            'TEXT_ORDER_ALLOCATE_DEFICIT' => 'Order allocation deficit',
            'TEXT_ALLOCATE_TIME' => 'Allocation time'
        ]);
        $this->addTranslation('admin/main', [
            'TEXT_MINUTES_COMMON' => 'minutes'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/categories', [
            'TEXT_TEMPORARY_STOCK',
            'TEXT_BACKEND_TEMPORARY_STOCK_DELETE_CONFIRM',
            'TEXT_BACKEND_ALLOCATED_STOCK',
            'TEXT_LOCATION',
            'TEXT_ORDER_ALLOCATE_DEFICIT',
            'TEXT_ALLOCATE_TIME'
        ]);
        $this->removeTranslation('admin/main', [
            'TEXT_MINUTES_COMMON'
        ]);
    }
}