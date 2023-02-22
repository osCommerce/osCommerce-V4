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
 * Class m230118_153127_account_captcha
 */
class m230118_153127_account_captcha extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'CAPTCHA_ON_CUSTOMER_LOGIN'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_key' => 'CAPTCHA_ON_CUSTOMER_LOGIN',
                'configuration_title' => 'Captcha on customer login',
                'configuration_description' => 'Captcha on customer login',
                'configuration_group_id' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
                'configuration_value' => 'False',
                'sort_order' => '200',
                'date_added' => (new yii\db\Expression('now()')),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
            ]);
        }

        $check = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'CAPTCHA_ON_CREATE_ACCOUNT'])->exists();
        if (!$check) {
            $this->insert('configuration', [
                'configuration_key' => 'CAPTCHA_ON_CREATE_ACCOUNT',
                'configuration_title' => 'Captcha on create account',
                'configuration_description' => 'Captcha on create account',
                'configuration_group_id' => 'BOX_CONFIGURATION_CUSTOMER_DETAILS',
                'configuration_value' => 'False',
                'sort_order' => '200',
                'date_added' => (new yii\db\Expression('now()')),
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'),'
            ]);
        }

        $themes = \common\models\Themes::find()->asArray()->all();
        foreach ($themes as $theme) {
            $this->updateTheme($theme['theme_name'], 'lib/console/migrations/themes/m230118_153127_account_captcha.json');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
