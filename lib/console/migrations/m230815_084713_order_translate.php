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
 * Class m230815_084713_order_translate
 */
class m230815_084713_order_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/orders', [
            'SAVE_CART' => 'Save cart',
            'RESET_CART' => 'Reset cart',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
