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
 * Class m220916_000909_admin_stock_detail_refresh
 */
class m220916_000909_admin_stock_detail_refresh extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $q = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'STOCK_ADMIN_AUTO_REFRESH'])->count();
        if ($q == 0) {
          $this->insert('configuration',[
              'configuration_key' => 'STOCK_ADMIN_AUTO_REFRESH',
              'configuration_title' => 'Admin stock auto refresh box',
              'configuration_value' => '30',
              'configuration_description' => 'Refresh Stock info on edit product page (seconds). Min value 5. Disabled if less.',
              'configuration_group_id' => 'TEXT_STOCK',
              'sort_order' => 10,
              'date_added' => new \yii\db\Expression('NOW()'),
              'set_function' => '',
          ]);
        }
        $q = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'STOCK_ADMIN_REFRESH_ON_CLICK'])->count();
        if ($q == 0) {
          $this->insert('configuration',[
              'configuration_key' => 'STOCK_ADMIN_REFRESH_ON_CLICK',
              'configuration_title' => 'Admin stock refresh on click',
              'configuration_value' => 'True',
              'configuration_description' => 'Refresh Stock info on click on any stock-related link',
              'configuration_group_id' => 'TEXT_STOCK',
              'sort_order' => 10,
              'date_added' => new \yii\db\Expression('NOW()'),
              'use_function' => '',
              'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
          ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m220916_000909_admin_stock_detail_refresh cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220916_000909_admin_stock_detail_refresh cannot be reverted.\n";

        return false;
    }
    */
}
