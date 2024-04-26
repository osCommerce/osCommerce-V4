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
 * Class m231011_095359_theme_styles_groups
 */
class m231011_095359_theme_styles_groups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isTableExists('themes_styles_groups')) {
            $this->createTable('themes_styles_groups', [
                'theme_name' => $this->string(255)->notNull()->defaultValue(''),
                'group_id' => $this->integer(11)->notNull()->defaultValue(1),
                'group_name' => $this->string(255)->notNull()->defaultValue(''),
                'sort_order' => $this->integer(11)->notNull()->defaultValue(0),
            ]);
            $this->createIndex('idx_themes_styles_groups', 'themes_styles_groups', ['group_id', 'group_name']);
            $this->addPrimaryKey('', 'themes_styles_groups', ['theme_name', 'group_id']);

            foreach (\common\models\Themes::find()->asArray()->all() as $theme) {
                $this->insert('themes_styles_groups', [
                    'theme_name' => $theme['theme_name'],
                    'group_id' => 1,
                    'group_name' => 'Main',
                    'sort_order' => '1',
                ]);
            }
        }

        if ($this->isTableExists('themes_styles_main')) {
            if (!$this->isFieldExists('group_id', 'themes_styles_main')) {
                $this->addColumn('themes_styles_main', 'group_id', $this->integer(11)->notNull()->defaultValue(1));
            }
            if ($this->isFieldExists('main_style', 'themes_styles_main')) {
                $this->dropColumn('themes_styles_main', 'main_style');
            }
        }

        $this->addTranslation('admin/design', [
            'COLOR_VAR' => 'Color var',
            'COLOR_HSL_OPACITY' => 'Color hsl (opacity)',
            'FONT_VAR' => 'Font var',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
