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
 * Class m230307_183107_2level_attribute_selection
 */
class m230307_183107_2level_attribute_selection extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'CONDITION_2LEVEL_ATTRIBUTE_SELECTION'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_title' => '2-Level Attribute Selection Condition',
                'configuration_key' => 'CONDITION_2LEVEL_ATTRIBUTE_SELECTION',
                'configuration_value' => '0',
                'configuration_description' => 'Use 2-level attribute selection if the number of attribute values more than X.',
                'configuration_group_id' => 'TEXT_LISTING_PRODUCTS',
                'sort_order' => 0,
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }
        $this->addTranslation('main', [
            'TEXT_2LEVEL_ATTRIBUTE_SELECTION_XX_OPTIONS' => '%s ... (%s options)',
        ]);

        $this->db->createCommand(
            "update translation set translation_value = 'Show \"Please select\" option in product attributes' where translation_key = 'PRODUCTS_ATTRIBUTES_SHOW_SELECT_TITLE' and translation_entity = 'configuration'"
        )->execute();
        $this->db->createCommand(
            "update translation set translation_value = 'Show \"Please select\" option in product attributes' where translation_key = 'PRODUCTS_ATTRIBUTES_SHOW_SELECT_DESC' and translation_entity = 'configuration'"
        )->execute();
        $this->db->createCommand(
            "update configuration set configuration_title = 'Show \"Please select\" option in product attributes', configuration_description = 'Show \"Please select\" option in product attributes' where configuration_key = 'PRODUCTS_ATTRIBUTES_SHOW_SELECT'"
        )->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230307_183107_2level_attribute_selection cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230307_183107_2level_attribute_selection cannot be reverted.\n";

        return false;
    }
    */
}
