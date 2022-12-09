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

namespace backend\design;

use Yii;
use yii\base\Widget;

class ComponentsButton extends Widget
{

    public $editor;
    public $platform_id;
    public $languages_id;
    public $buttons = ['banner', 'component', 'component-html'];

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $languages_id;

        $platform_id = $this->platform_id ? $this->platform_id : \common\classes\platform::defaultId();
        $lang_id = $this->languages_id ? $this->languages_id : $languages_id;

        return $this->render('components-button.tpl', [
            'content_widget_url' => \Yii::$app->urlManager->createUrl([
                'design/content-widget',
                'name'=>'Banner',
                'editor_id' => $this->editor,
                'languages_id' => $languages_id,
                'platform_id' => $platform_id
            ]),
            'url' => \Yii::$app->urlManager->createUrl([
                'information_manager/component-keys',
                'name'=>'components',
                'editor_id' => $this->editor,
                'languages_id' => $languages_id,
                'platform_id' => $platform_id
            ]),
            'url2' => \Yii::$app->urlManager->createUrl([
                'information_manager/component-keys',
                'name'=>'components',
                'editor_id' => $this->editor,
                'languages_id' => $languages_id,
                'platform_id' => $platform_id,
                'html' => 1
            ]),
            'editor' => $this->editor,
            'platform_id' => $platform_id,
            'languages_id' => $this->languages_id ? $this->languages_id : $lang_id,
            'action' => 'information_manager/page-links',
            'buttons' => $this->buttons
        ]);
    }
}