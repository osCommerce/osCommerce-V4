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
 * Class m230921_103117_css_version
 */
class m230921_103117_css_version extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!(\common\models\Configuration::findOne(['configuration_key' => 'BACKEND_CSS_VERSION']) instanceof \common\models\Configuration)) {
            $this->insert('configuration', [
                'configuration_title' => 'CSS version for the admin aria',
                'configuration_key' => 'BACKEND_CSS_VERSION',
                'configuration_value' => '30',
                'configuration_description' => 'It is used to update the CSS cache in the user\'s browser.',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
                'sort_order' => 100,
                'date_added' => date('Y-m-d H:i:s')
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
