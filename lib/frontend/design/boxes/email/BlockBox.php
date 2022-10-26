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

namespace frontend\design\boxes\email;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Block;

class BlockBox extends Widget
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
        $type = (int)$settings[0]['block_type'];
        $block_id = 'block-' . $id;
        if (in_array($type, [2, 4, 5, 6, 7, 9, 10, 11, 12])){
            return [$block_id, $block_id . '-2'];
        } elseif (in_array($type, [3, 8, 13])){
            return [$block_id, $block_id . '-2', $block_id . '-3'];
        } elseif ($type === 14){
            return [$block_id, $block_id . '-2', $block_id . '-3', $block_id . '-4'];
        } elseif ($type === 15){
            return [$block_id, $block_id . '-2', $block_id . '-3', $block_id . '-4', $block_id . '-5'];
        } elseif ($type === 1){
            return [$block_id];
        } else {
            return [];
        }
    }

    public function run()
    {
        $type = (int)$this->settings[0]['block_type'];
        $var = '';
        $widthArr = [100];

        switch ($type) {
            case 1: $widthArr = [100]; break;
            case 2: $widthArr = [50, 50]; break;
            case 3: $widthArr = [33, 33, 33]; break;
            case 4: $widthArr = [66, 33]; break;
            case 5: $widthArr = [33, 66]; break;
            case 6: $widthArr = [25, 75]; break;
            case 7: $widthArr = [75, 25]; break;
            case 8: $widthArr = [25, 50, 25]; break;
            case 9: $widthArr = [20, 80]; break;
            case 10: $widthArr = [80, 20]; break;
            case 11: $widthArr = [40, 60]; break;
            case 12: $widthArr = [60, 40]; break;
            case 13: $widthArr = [20, 60, 20]; break;
            case 14: $widthArr = [25, 25, 25, 25]; break;
            case 15: $widthArr = [20, 20, 20, 20, 20]; break;
        }

        if (!isset($this->params['blockTreeData']) || !is_array($this->params['blockTreeData'])) {
            return $var;
        }

        $var = '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>';

        $width = $widthArr[0];
        foreach ($this->params['blockTreeData'] as $blockId => $block) {
            $this->params['blockTreeData'] = $block;

            $var .= '<td width="' . $width . '%">';
            $var .= Block::widget(['name' => $blockId, 'params' => ['params' => $this->params]]);
            $var .= '</td>';

            $width = next($widthArr);
        }

        $var .= '</tr></table>';

        return $var;
    }
}