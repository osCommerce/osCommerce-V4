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
 * Class m230816_132155_search_history
 */
class m230816_132155_search_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design', [
            'REMEMBER_HISTORY_ITEMS' => 'Remember history items',
            'SHOW_SEARCH_HISTORY' => 'Show search history',
        ]);
        $this->addTranslation('main', [
            'SEARCH_HISTORY' => 'Search history',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
