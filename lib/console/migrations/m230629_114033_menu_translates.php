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
 * Class m230629_114033_menu_translates
 */
class m230629_114033_menu_translates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/menus', [
            'TEXT_SWAP' => 'Swap',
            'TEXT_SWAP_MENU' => 'Swap menu',
            'TEXT_SWAP_WITH' => 'Swap with',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
