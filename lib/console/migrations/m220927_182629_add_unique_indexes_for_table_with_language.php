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
 * Class m220927_182629_add_unique_indexes_for_table_with_language
 */
class m220927_182629_add_unique_indexes_for_table_with_language extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = TABLE_COUPONS_DESCRIPTION;
        $count = $this->db->createCommand("SELECT COUNT(*) FROM $table GROUP BY language_id, coupon_id HAVING COUNT(*) > 1")->queryScalar();
        if ($count > 0) {
            $this->db->createCommand("CREATE TABLE ${table}_copy LIKE $table")->execute();
            $this->db->createCommand("INSERT INTO ${table}_copy SELECT * FROM $table GROUP BY language_id, coupon_id")->execute();
            $this->db->createCommand("DROP TABLE $table")->execute();
            $this->db->createCommand("ALTER TABLE ${table}_copy RENAME TO $table")->execute();
        }
        $this->createIndex('language_coupon', $table, 'language_id,coupon_id', true);

        $table = TABLE_DESIGN_BOXES_SETTINGS;
        $count = $this->db->createCommand("SELECT COUNT(*) FROM $table GROUP BY box_id, language_id, setting_name, visibility HAVING COUNT(*) > 1")->queryScalar();
        if ($count > 0) {
            $affected = $this->db->createCommand("DELETE t1 FROM $table t1 INNER JOIN $table t2 WHERE t1.id > t2.id AND t1.box_id = t2.box_id AND t1.language_id = t2.language_id AND t1.setting_name = t2.setting_name AND t1.visibility = t2.visibility")->execute();
            \Yii::warning("Dup records found for $table: $count row(s). Deleted $affected row(s)");
        }
        $this->createIndex('language_box_settingname_visibility', $table, 'language_id,box_id,setting_name,visibility', true);

        $table = TABLE_DESIGN_BOXES_SETTINGS_TMP;
        $count = $this->db->createCommand("SELECT COUNT(*) FROM $table GROUP BY box_id, language_id, setting_name, visibility HAVING COUNT(*) > 1")->queryScalar();
        if ($count > 0) {
            $affected = $this->db->createCommand("DELETE t1 FROM $table t1 INNER JOIN $table t2 WHERE t1.id > t2.id AND t1.box_id = t2.box_id AND t1.language_id = t2.language_id AND t1.setting_name = t2.setting_name AND t1.visibility = t2.visibility")->execute();
            \Yii::warning("Dup records found for $table: $count row(s). Deleted $affected row(s)");
        }
        $this->createIndex('language_box_settingname_visibility', $table, 'language_id,box_id,setting_name,visibility', true);

        $table = TABLE_BANNERS_LANGUAGES;
        $count = $this->db->createCommand("SELECT COUNT(*) FROM $table GROUP BY language_id, banners_id, platform_id HAVING COUNT(*) > 1")->queryScalar();
        if ($count > 0) {
            $affected = $this->db->createCommand("DELETE t1 FROM $table t1 INNER JOIN $table t2 WHERE t1.blang_id > t2.blang_id AND t1.language_id = t2.language_id AND t1.banners_id = t2.banners_id AND t1.platform_id = t2.platform_id")->execute();
            \Yii::warning("Dup records found for $table: $count row(s). Deleted $affected row(s)");
        }
        $this->createIndex('language_banners_platform', $table, 'language_id,banners_id,platform_id', true);

        $table = TABLE_MENU_TITLES;
        $count = $this->db->createCommand("SELECT COUNT(*) FROM $table GROUP BY language_id, item_id HAVING COUNT(*) > 1")->queryScalar();
        if ($count > 0) {
            $affected = $this->db->createCommand("DELETE t1 FROM $table t1 INNER JOIN $table t2 WHERE t1.id > t2.id AND t1.language_id = t2.language_id AND t1.item_id = t2.item_id")->execute();
            \Yii::warning("Dup records found for $table: $count row(s). Deleted $affected row(s)");
        }

        $this->createIndex('language_item', $table, 'language_id,item_id', true);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220927_182629_add_unique_indexes_for_table_with_language cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220927_182629_add_unique_indexes_for_table_with_language cannot be reverted.\n";

        return false;
    }
    */
}
