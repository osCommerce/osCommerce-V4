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
use common\models\Themes;
use common\models\ThemesStyles;
use common\models\ThemesStylesMain;

/**
 * Class m230713_085019_message_styles
 */
class m230713_085019_message_styles extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumnIfMissing('themes_styles_main', 'main_style', $this->integer(1)->notNull()->defaultValue(0));

        $themeNames = [];
        $themes = Themes::find()->where(['not', ['theme_name' => 'backend']])->asArray()->all();
        foreach ($themes as $theme) {
            $themeNames[] = $theme['theme_name'];
            $themeNames[] = $theme['theme_name'] . '-mobile';
        }

        foreach ($themeNames as $themeName) {
            $successColor = ThemesStylesMain::findOne(['theme_name' => $themeName, 'name' => 'success-color']);
            if (!$successColor) {
                $successColor = new ThemesStylesMain();
                $successColor->theme_name = $themeName;
                $successColor->name = 'success-color';
                $successColor->value = '#00A858';
                $successColor->type = 'color';
                $successColor->sort_order = '7';
                $successColor->save();
            }

            $messages = [
                'danger' => 'attention',
                'info' => 'message',
                'success' => 'success',
                'warning' => 'warning'
            ];
            foreach ($messages as $messageName => $colorName) {
                $messageColor = ThemesStyles::findOne([
                    'theme_name' => $themeName,
                    'selector' => '.b-info .' . $messageName . '-message',
                    'attribute' => 'color'
                ]);
                if (!$messageColor) {
                    $messageColor = new ThemesStyles();
                    $messageColor->theme_name = $themeName;
                    $messageColor->selector = '.b-info .' . $messageName . '-message';
                    $messageColor->attribute = 'color';
                    $messageColor->value = '$' . $colorName . '-color';
                    $messageColor->accessibility = '.b-info';
                    $messageColor->save();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
