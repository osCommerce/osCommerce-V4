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
 * Class m220831_132104_stock_deficit_translation
 */
class m220831_132104_stock_deficit_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->safeDown();
        $this->addTranslation('admin/main', [
            'TEXT_STOCK_DEFICIT_QUANTITY' => 'Deficit',
            'TEXT_STOCK_OVERALLOCATED_QUANTITY' => 'Overallocated',
            'TEXT_STOCK_TEMPORARY_QUANTITY' => 'In cart',
            'TEXT_TEMPORARY_STOCK' => 'In cart stock',
            'TEXT_STOCK_TEMPORARY_ALLOCATED' => 'Not paid order',
            'TEXT_AUTOALLOCATE' => 'Allocate automatically'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/main', [
            'TEXT_STOCK_DEFICIT_QUANTITY',
            'TEXT_STOCK_OVERALLOCATED_QUANTITY',
            'TEXT_STOCK_TEMPORARY_QUANTITY',
            'TEXT_TEMPORARY_STOCK',
            'TEXT_STOCK_TEMPORARY_ALLOCATED',
            'TEXT_AUTOALLOCATE'
        ]);
    }
}