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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Block;
use frontend\design\Info;

class Tabs extends Widget
{

    public $settings;
    public $params;
    public $id;

    public function init()
    {
        parent::init();
    }

    public static function children($id, $settings, $themeName)
    {
        if (Info::isAdmin()) {
            $designBoxes = \common\models\DesignBoxesTmp::find();
        } else {
            $designBoxes = \common\models\DesignBoxes::find();
        }
        $boxes = $designBoxes
            ->where([
                'theme_name' => $themeName,
            ])
            ->andWhere(['like', 'block_name', 'block-' . $id])
            ->asArray()->all();

        $tabs = [];
        foreach ($boxes as $box) {
            if (!in_array($box['block_name'], $tabs)) {
                $tabs[] = $box['block_name'];
            }
        }
        return $tabs;
    }

    public function run()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $accordion = [];
        if (isset($this->settings['visibility']['accordion']) && is_array($this->settings['visibility']['accordion'])) {
            foreach ($this->settings['visibility']['accordion'] as $width => $val) {
                $accordion[] = $width;
            }
        }

        $block_id = 'block-' . $this->id;
        $tabs_headings = '';
        $var = '';
        $var_content = '';

        $blockTreeData = $this->params['blockTreeData'];

        for($i = 1; $i <= 10; $i++){

            if (isset($this->settings[0]['tab_' . $i])) {
                $title = $this->settings[0]['tab_' . $i];
            } elseif (isset($this->settings[$languages_id]['tab_' . $i])){
                $title = $this->settings[$languages_id]['tab_' . $i];
            } else {
                continue;
            }
            $title = \frontend\design\Info::translateKeys($title);

            $this->params['blockTreeData'] = $blockTreeData[$block_id . '-' . $i];
            $widget = Block::widget(['name' => $block_id . '-' . $i, 'params' => ['params' => $this->params, 'cols' => $i, 'tabs' => true]]);

            if (($widget || Info::isAdmin())) {

                $var_content .= '<div
                    class="accordion-heading tab-' . $block_id . '-' . $i . '"
                    data-tab="tab-' . $block_id . '-' . $i . '"
                    data-href="#tab-' . $block_id . '-' . $i . '"
                    style="display: none"><span>' . $title . '</span></div>';

                $var_content .= $widget;

                $tabs_headings .= '<div
                    class="tab-' . $block_id . '-' . $i . ' tab-li"
                    data-tab="tab-' . $block_id . '-' . $i . '"
                    ><a class="tab-a" data-href="#tab-' . $block_id . '-' . $i . '">' . $title . '</a></div>';
            }
        }


        if ($var_content) {

            $var .= '<div class="tab-navigation">' . $tabs_headings . '</div>';
            $var .= $var_content;

            $var .= IncludeTpl::widget(['file' => 'boxes/tabs.tpl', 'params' => [
                'id' => $this->id,
                'accordion' => $accordion
            ]]);

            return $var;

        } else {

            return "";

        }

    }
}