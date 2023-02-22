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
 * Class m221215_144105_banners
 */
class m221215_144105_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',[
            'ADD_BANNER' => 'Add banner',
            'ADD_COMPONENT_KEY' => 'Add component key',
            'ADD_COMPONENT_HTML' => 'Add component html',
            'CHOOSE_BANNER_TYPE' => 'Choose banner type',
            'SINGLE_BANNER' => 'Single banner',
            'ALL_BANNERS_IN_GROUP' => 'All banners in group',
            'RANDOM_BANNER' => 'Random banner',
            'TEXT_TEMPLATE' => 'Template',
            'TEXT_PRELOAD' => 'Preload',
            'LAZY_LOAD_IMAGES' => 'Lazy load images',
            'TEXT_DOTS' => 'Dots',
            'CENTER_MODE' => 'Center mode',
            'ADAPTIVE_HEIGHT' => 'Adaptive height',
            'TEXT_AUTOPLAY' => 'Autoplay',
            'AUTOPLAY_SPEED' => 'Autoplay speed',
            'TEXT_SPEED' => 'Speed',
            'TEXT_FADE' => 'Fade',
            'EASING_FUNCTION' => 'Easing function',
            'TEXT_UNDER_IMAGE' => 'Text under image',
        ]);

        $themes = \common\models\Themes::find()->asArray()->all();
        foreach ($themes as $theme) {
            $this->updateTheme($theme['theme_name'], 'lib/console/migrations/themes/m221116_104402_banners.json');
            $this->updateTheme($theme['theme_name'] . '-mobile', 'lib/console/migrations/themes/m221116_104402_banners.json');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221215_144105_banners cannot be reverted.\n";

        return false;
    }
}
