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
 * Class m231103_095453_main_styles_tabs
 */
class m231103_095453_main_styles_tabs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('tab', 'themes_styles_groups') ){
            $this->addColumn('themes_styles_groups', 'tab', $this->string(128)->notNull());
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
