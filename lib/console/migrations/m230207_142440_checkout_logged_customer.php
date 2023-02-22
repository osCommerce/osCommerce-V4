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
 * Class m230207_142440_checkout_logged_customer
 */
class m230207_142440_checkout_logged_customer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('checkout_logged_customer', 'platforms')) {
            $this->addColumn('platforms', 'checkout_logged_customer', $this->tinyInteger(1)->notNull()->defaultValue(0));
        }

        $this->addTranslation('admin/platforms', [
            'CHECKOUT_ONLY_FOR_LOGGED_CUSTOMERS' => 'Checkout only for logged customers'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
