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

  public function run()
  {
    $type = $this->settings[0]['block_type'];
    $block_id = 'block-' . $this->id;
    $var = '';

    /*if ($type == 2 || $type == 4 || $type == 5 || $type == 6 || $type == 7 || $type == 9 || $type == 10 || $type == 11 || $type == 12){
      $var .= Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
      $var .= Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
    } elseif ($type == 3 || $type == 8 || $type == 13){
      $var .= Block::widget(['name' => $block_id]);
      $var .= Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
      $var .= Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
    } elseif ($type == 14){
      $var .= Block::widget(['name' => $block_id]);
      $var .= Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
      $var .= Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
      $var .= Block::widget(['name' => $block_id . '-4', 'params' => ['params' => $this->params, 'cols' => 4]]);
    } elseif ($type == 15){
      $var .= Block::widget(['name' => $block_id]);
      $var .= Block::widget(['name' => $block_id . '-2', 'params' => ['params' => $this->params, 'cols' => 2]]);
      $var .= Block::widget(['name' => $block_id . '-3', 'params' => ['params' => $this->params, 'cols' => 3]]);
      $var .= Block::widget(['name' => $block_id . '-4', 'params' => ['params' => $this->params, 'cols' => 4]]);
      $var .= Block::widget(['name' => $block_id . '-5', 'params' => ['params' => $this->params, 'cols' => 5]]);
    } elseif ($type == 1){
      $var .= Block::widget(['name' => $block_id, 'params' => ['params' => $this->params]]);
    } else {
      $var .= '<div class="no-block-settings"></div>
  <script type="text/javascript">
    (function($){ $(function(){ setTimeout(function(){
      $(\'.no-block-settings\').closest(\'.box-block\').find(\'.edit-box\').trigger(\'click\')
    }, 2000)  }) })(jQuery)
  </script>';
    }*/

    //return $var;

    return IncludeTpl::widget([
      'file' => 'boxes/email/block.tpl',
      'params' => [
        'block_id_1' => $block_id,
        'block_id_2' => $block_id . '-2',
        'block_id_3' => $block_id . '-3',
        'block_id_4' => $block_id . '-4',
        'block_id_5' => $block_id . '-5',
        'params' => $this->params,
        'type' => $type,
      ]
    ]);
  }
}