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
 * Class m230418_202057_speed_up_themes_flushing
 */
class m230418_202057_speed_up_themes_flushing extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // moved to m230426_132026_fix_theme_style_indexes
        //$this->createIndex('idx_theme_name', 'design_boxes_cache', 'theme_name');
        //$this->createIndex('idx_accesibility_media_selector_attr_vis', 'themes_styles', 'accessibility, media, selector, attribute, visibility'); // for cache flush
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230418_202057_speed_up_themes_flushing cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230418_202057_speed_up_themes_flushing cannot be reverted.\n";

        return false;
    }
    */
}
