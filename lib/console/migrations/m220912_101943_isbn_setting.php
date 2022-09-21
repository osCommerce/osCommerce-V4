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
 * Class m220912_101943_isbn_setting
 */
class m220912_101943_isbn_setting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'MODULE_NP_SENDER_AREA_REF'])->exists();
        if (!$check) {
            $this->insert('configuration',[
                'configuration_title' => 'Show ISBN',
                'configuration_key' => 'SHOW_ISBN',
                'configuration_value' => 'False',
                'configuration_description' => 'Show ISBN',
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
    }
}
