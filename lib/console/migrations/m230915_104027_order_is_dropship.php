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
 * Class m230915_104027_order_is_dropship
 */
class m230915_104027_order_is_dropship extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isTableExists('orders')) {
            if (!$this->isFieldExists('drop_ship', 'orders')) {
                $this->addColumn('orders', 'drop_ship', $this->integer(1)->notNull()->defaultValue(0));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}