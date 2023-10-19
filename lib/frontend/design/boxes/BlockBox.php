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
    $type = $this->settings[0]['block_type'];
    $block_id = 'block-' . $this->id;
    
    $to_pdf = (int)Yii::$app->request->get('to_pdf', 0);
    if ($to_pdf) {

      $block_1 = '';
      $block_2 = '';
      $block_3 = '';
      $block_4 = '';
      $block_5 = '';
      if ($type == 2 || $type == 4 || $type == 5 || $type == 6 || $type == 7 || $type == 9 || $type == 10 || $type == 11 || $type == 12){
        $block_1 = Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
        $block_2 = Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
      } elseif ($type == 3 || $type == 8 || $type == 13){
        $block_1 = Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
        $block_2 = Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
        $block_3 = Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
      } elseif ($type == 14){
        $block_1 = Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
        $block_2 = Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
        $block_3 = Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
        $block_4 = Block::widget(['name' => $block_id . '-4', 'params' => ['params' => $this->params, 'cols' => 4]]);
      } elseif ($type == 15){
        $block_1 = Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
        $block_2 = Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
        $block_3 = Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
        $block_4 = Block::widget(['name' => $block_id . '-4', 'params' => ['params' => $this->params, 'cols' => 4]]);
        $block_5 = Block::widget(['name' => $block_id . '-5', 'params' => ['params' => $this->params, 'cols' => 5]]);
      } elseif ($type == 1){
        $block_1 = Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
      }

      return IncludeTpl::widget([
        'file' => 'boxes/invoice/block.tpl',
        'params' => [
          'block_1' => $block_1,
          'block_2' => $block_2,
          'block_3' => $block_3,
          'block_4' => $block_4,
          'block_5' => $block_5,
          'type' => $type,
          'p_width' => Info::blockWidth($this->id)
        ]
      ]);
    }
    
    $var = '';


    if (!isset($this->params['blockTreeData']) || !is_array($this->params['blockTreeData'])) {
        return $var;
    }

    foreach ($this->params['blockTreeData'] as $blockId => $block) {
        $this->params['blockTreeData'] = $block;
        $var .= Block::widget(['name' => $blockId, 'params' => ['params' => $this->params]]);
    }

    return $var;
  }
}