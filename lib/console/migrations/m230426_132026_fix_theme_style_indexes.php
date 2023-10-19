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
 * Class m230426_132026_fix_theme_style_indexes
 */
class m230426_132026_fix_theme_style_indexes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->db->createCommand("ALTER TABLE design_boxes_settings MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE design_boxes_settings_tmp MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE design_backups MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE themes_steps MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();

        $this->dropIndex('theme_name','design_boxes');
        $this->db->createCommand("ALTER TABLE design_boxes MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE design_boxes MODIFY block_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->createIndex('idx_theme_name_block_name', 'design_boxes', 'theme_name, block_name');

        $this->dropIndex('theme_name','design_boxes_tmp');
        $this->db->createCommand("ALTER TABLE design_boxes_tmp MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE design_boxes_tmp MODIFY block_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->createIndex('idx_theme_name_block_name', 'design_boxes_tmp', 'theme_name, block_name');

        $this->dropIndex('idx_theme_name','design_boxes_cache');
        $this->db->createCommand("ALTER TABLE design_boxes_cache MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->db->createCommand("ALTER TABLE design_boxes_cache MODIFY block_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->createIndex('idx_theme_name2', 'design_boxes_cache', 'theme_name');

        $this->dropIndex('idx_accesibility_media_selector_attr_vis','themes_styles');
        $this->dropIndex('idx_themes_styles_attribute_theme_name','themes_styles');
        $this->db->createCommand('ALTER TABLE themes_styles CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci;')->execute();
        $this->db->createCommand("ALTER TABLE themes_styles MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->createIndex('idx_accesibility_media_selector_attr_vis2', 'themes_styles', 'accessibility, media, selector, attribute, visibility');
        $this->createIndex('idx_theme_name_attribute','themes_styles', 'theme_name, attribute');

        $this->dropIndex('theme_name','themes_styles_cache');
        $this->db->createCommand('ALTER TABLE themes_styles_cache CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci;')->execute();
        $this->db->createCommand("ALTER TABLE themes_styles_cache MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
        $this->createIndex('idx_theme_name','themes_styles_cache', 'theme_name');

        $this->db->createCommand('ALTER TABLE themes_styles_tmp CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci;')->execute();
        $this->db->createCommand("ALTER TABLE themes_styles_tmp MODIFY theme_name VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';")->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230426_132026_fix_theme_style_indexes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230426_132026_fix_theme_style_indexes cannot be reverted.\n";

        return false;
    }
    */
}
