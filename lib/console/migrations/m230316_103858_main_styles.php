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
 * Class m230316_103858_main_styles
 */
class m230316_103858_main_styles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isTableExists('themes_styles_main')) {
            $this->createTable('themes_styles_main', [
                'theme_name' => $this->string(255)->notNull()->defaultValue(''),
                'name' => $this->string(255)->notNull()->defaultValue(''),
                'value' => $this->string(255)->notNull()->defaultValue(''),
                'type' => $this->string(255)->notNull()->defaultValue(''),
                'sort_order' => $this->integer(11)->notNull()->defaultValue(0),
            ]);
            $this->createIndex('idx_themes_styles_main', 'themes_styles_main', ['theme_name', 'name']);
            $this->addPrimaryKey('', 'themes_styles_main', ['theme_name', 'name']);
        }

        $this->addTranslation('admin/design', [
            'STYLE_NAME' => 'Style name',
            'STYLE_VALUE' => 'Style value',
            'ADD_COLOR' => 'Add color',
            'ENTER_STYLE_NAME' => 'Enter style name',
            'ENTER_STYLE_VALUE' => 'Enter style value',
            'THIS_STYLE_PLACED_IN' => 'This style placed in %s places',
            'THIS_STYLE_NAME_EXISTS' => 'This style name already exists',
            'CHANGED_MAIN_STYLES' => 'changed main styles',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
