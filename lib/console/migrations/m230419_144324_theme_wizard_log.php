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
 * Class m230419_144324_theme_wizard_log
 */
class m230419_144324_theme_wizard_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'REMOVED_PAGE_TEMPLATE' => 'removed page template',
            'CHANGED_STYLES' => 'changed styles',
            'COPIED_PAGE' => 'copied page',
            'IMPORTED_THEME' => 'imported theme',
            'APPLIED_MIGRATION' => 'applied migration',
            'SET_WIDGET_GROUP' => 'set widget group',
            'SET_MAIN_THEME_STYLES' => 'set main theme styles',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
