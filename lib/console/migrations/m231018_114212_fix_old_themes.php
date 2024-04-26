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
 * Class m231018_114212_fix_old_themes
 */
class m231018_114212_fix_old_themes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $themesVars = [
            'watch' => [
                'colors' => [
                    'attention-color' => '#EE4225',
                    'message-color' => '#00A858',
                    'success-color' => '#00A858',
                    'warning-color' => '#FF6D00',
                    'font-color-1' => '#1DA1F2',
                    'background-color-1' => '#EEEEEE',
                    'background-color-3' => '#FFFFFF',
                ]
            ],
            'watch-mobile' => [
                'font' => [
                    'font-icons-2' => 'trueloaded',
                ],
                'colors' => [
                    'attention-color' => '#EE4225',
                    'message-color' => '#00A858',
                    'success-color' => '#00A858',
                    'warning-color' => '#FF6D00',
                    'font-color-1' => '#1DA1F2',
                    'background-color-1' => '#EEEEEE',
                    'background-color-3' => '#FFFFFF',
                    'font-color-reverse' => '#FFFFFF',
                ]
            ],
            'furniture' => [
                'colors' => [
                    'attention-color' => '#ED1B24',
                    'message-color' => '#04A440',
                    'success-color' => '#04A440',
                    'warning-color' => '#FF6D00',
                    'font-color-5' => '#FF6D00',
                    'background-color-7' => '#FFFFFF',
                    'background-color-3' => '#E6E6ED',
                ]
            ],
            'furniture-mobile' => [
                'colors' => [
                    'attention-color' => '#ED1B24',
                    'message-color' => '#04A440',
                    'success-color' => '#04A440',
                    'warning-color' => '#FF6D00',
                    'background-color-7' => '#FFFFFF',
                    'background-color-3' => '#E6E6ED',
                ]
            ],
            'printshop' => [
                'font' => [
                    'font-icons-1' => 'themify',
                    'font-icons-2' => 'FontAwesome',
                ],
                'colors' => [
                    'attention-color' => '#EE4225',
                    'message-color' => '#00A858',
                    'success-color' => '#00A858',
                    'warning-color' => '#EEB32C',
                    'background-color-1' => '#EEEEEE',
                    'background-color-6' => '#FFFFFF',
                ]
            ],
            'deals' => [
                'colors' => [
                    'attention-color' => '#ED1B24',
                    'message-color' => '#00A858',
                    'success-color' => '#00A858',
                    'warning-color' => '#FF6D00',
                    'background-color-3' => '#FFFFFF',
                    'background-color-2' => '#EEEEEE',
                ]
            ],
        ];

        foreach ($themesVars as $theme_name => $typesVars ) {
            if (!\common\models\ThemesStyles::findOne(['theme_name' => $theme_name])) {
                continue;
            }
            foreach ($typesVars as $type => $vars) {
                foreach ($vars as $name => $value) {
                    if (!\common\models\ThemesStylesMain::findOne(['theme_name' => $theme_name, 'name' => $name])){
                        $themesStylesMain = new \common\models\ThemesStylesMain();
                        $themesStylesMain->theme_name = $theme_name;
                        $themesStylesMain->name = $name;
                        $themesStylesMain->value = $value;
                        $themesStylesMain->type = $type;
                        $themesStylesMain->save(false);
                    }
                }
            }
        }
        \backend\design\Style::flushCacheAll();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
