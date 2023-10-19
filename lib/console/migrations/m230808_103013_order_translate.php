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
 * Class m230808_103013_order_translate
 */
class m230808_103013_order_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'QUANTITY_DISCOUNT_DIFFERENT' => 'Quantity discount is different form product saved price. Do you want to change product price?',
            'TEXT_CHANGE_TO' => 'Change to',
            'TEXT_LEAVE' => 'Leave',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
