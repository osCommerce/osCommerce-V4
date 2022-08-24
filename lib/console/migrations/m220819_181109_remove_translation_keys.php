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
 * Class m220819_181109_remove_translation_keys
 */
class m220819_181109_remove_translation_keys extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('wedding');
        $this->removeTranslation('testimonials');
        $this->removeTranslation('admin/main', [
            'BOX_REPORT_VOLO_BATCH_DETAIL', 
            'BOX_REPORT_VOLO_BATCH_SUMMARY',
            'BOX_CATALOG_COMPETITORS',
            'BOX_AFFILIATES',
            'BOX_AFFILIATE_AFFILIATE',
            'BOX_AFFILIATE_PAYMENT',
            'BOX_AFFILIATE_PROGRAM',
            'BOX_AMAZON',
            'BOX_AMAZON_PROFILES',
            'BOX_CATALOG_CATEGORIES_PC_TEMPLATES',
            'BOX_CATALOG_CONFIGURATOR',
            'BOX_CATALOG_CATEGORIES_CLASSES',
            'BOX_CATALOG_CATEGORIES_ELEMENTS',
            'BOX_CATALOG_CHAT',
        ]);
        \common\models\Translation::updateAll(['checked' => 1, 'translated' => 1], 'language_id = 1 AND (checked <> 1 OR translated <> 1)');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220819_181109_remove_translation_keys cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220819_181109_remove_translation_keys cannot be reverted.\n";

        return false;
    }
    */
}
