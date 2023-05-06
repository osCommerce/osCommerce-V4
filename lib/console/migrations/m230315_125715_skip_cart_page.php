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
 * Class m230315_125715_skip_cart_page
 */
class m230315_125715_skip_cart_page extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('configuration',[
            'configuration_title' => 'Skip shopping cart page',
            'configuration_key' => 'SKIP_CART_PAGE',
            'configuration_value' => 'False',
            'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
            'date_added' => new \yii\db\Expression('NOW()'),
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
