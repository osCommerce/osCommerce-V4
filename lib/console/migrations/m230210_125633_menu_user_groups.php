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
 * Class m230210_125633_menu_user_groups
 */
class m230210_125633_menu_user_groups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('user_groups', 'menu_items')) {
            $this->addColumn('menu_items', 'user_groups', $this->string(255)->notNull()->defaultValue('#0#'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
