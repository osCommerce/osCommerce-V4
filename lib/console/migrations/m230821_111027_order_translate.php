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
 * Class m230821_111027_order_translate
 */
class m230821_111027_order_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'ATTRIBUTE_PRICE_DIFFERENT' => 'Attribute price is different form product saved price. Do you want to change product price?',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
