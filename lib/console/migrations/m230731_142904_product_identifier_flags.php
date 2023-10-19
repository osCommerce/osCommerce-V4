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
 * Class m230731_142904_product_identifier_flags
 */
class m230731_142904_product_identifier_flags extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'SHOW_ASIN'])->exists();
        if (!$check) {
            $this->insert('configuration',[
                'configuration_title' => 'Show ASIN',
                'configuration_key' => 'SHOW_ASIN',
                'configuration_value' => 'True',
                'configuration_description' => 'Show ASIN',
                'configuration_group_id' => 'TEXT_STOCK',
                'sort_order' => 50,
                'date_added' => new \yii\db\Expression('NOW()'),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
            ]);
        }
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'SHOW_EAN'])->exists();
        if (!$check) {
            $this->insert('configuration',[
                'configuration_title' => 'Show EAN',
                'configuration_key' => 'SHOW_EAN',
                'configuration_value' => 'True',
                'configuration_description' => 'Show EAN',
                'configuration_group_id' => 'TEXT_STOCK',
                'sort_order' => 50,
                'date_added' => new \yii\db\Expression('NOW()'),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
            ]);
        }
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'SHOW_UPC'])->exists();
        if (!$check) {
            $this->insert('configuration',[
                'configuration_title' => 'Show UPC',
                'configuration_key' => 'SHOW_UPC',
                'configuration_value' => 'True',
                'configuration_description' => 'Show UPC',
                'configuration_group_id' => 'TEXT_STOCK',
                'sort_order' => 50,
                'date_added' => new \yii\db\Expression('NOW()'),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
            ]);
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230731_142904_product_identifier_flags cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230731_142904_product_identificator_flags cannot be reverted.\n";

        return false;
    }
    */
}
