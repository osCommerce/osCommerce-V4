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
 * Class m220901_142849_specials_sales_updates
 */
class m220901_142849_specials_sales_updates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/main', ['BOX_HEADING_SPECIALS_PROMOTE_TYPE']);
        $this->addTranslation('admin/main', ['BOX_HEADING_SPECIALS_PROMOTE_TYPE' => 'Frontend label']);
        
        $this->addAdminMenuAfter([
            'path' => 'specials-types',
            'title' => 'BOX_HEADING_SPECIALS_TAGS'
        ],'BOX_HEADING_FEATURED_TYPES');


        $this->db->createCommand(
                        "update translation " .
                            "set translation_value=:translation_value " .
                        "WHERE translation_entity=:entity AND translation_key=:translate_key AND translation_value=:translation_value_old ",
                        [
                          'entity' => 'admin/main',
                          'translation_value' => 'Q-ty limits',
                          'translation_value_old' => 'Q-ty limits.',
                          'translate_key' => 'TEXT_QTY_LIMITS'
            ])
            ->execute();
        $this->db->createCommand(
                        "update translation " .
                            "set translation_value=:translation_value " .
                        "WHERE translation_entity=:entity AND translation_key=:translate_key AND translation_value=:translation_value_old ",
                        [
                          'entity' => 'main',
                          'translation_value' => 'Q-ty limits',
                          'translation_value_old' => 'Q-ty limits.',
                          'translate_key' => 'TEXT_QTY_LIMITS'
            ])
            ->execute();

        $this->db->createCommand(
                        "update translation " .
                            "set translation_value= substr(translation_value, 1, length(translation_value)-1) " .
                        "WHERE translation_entity like :entity and translation_value like '%:' ",
                        [
                          'entity' => 'admin%'
            ])
            ->execute();

        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(), 'translation');


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m220901_142849_specials_sales_updates cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220901_142849_specials_sales_updates cannot be reverted.\n";

        return false;
    }
    */
}
