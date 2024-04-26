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
 * Class m240315_093052_menu_item_settings
 */
class m240315_093052_menu_item_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('settings', 'menu_items')) {
            $this->addColumn('menu_items', 'settings', $this->string(255));
        }
        $this->addTranslation('admin/menus', [
            'SORTING_FROM_CATALOGUE' => 'Sorting from catalogue'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
