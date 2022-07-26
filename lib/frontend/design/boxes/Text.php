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

class Text extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    if (isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']){
      return strip_tags($this->settings[$this->params['language_id']]['text'], '<br><div><span><b><strong><h1><h2><h3><h4><h5>');
    } else {
      return IncludeTpl::widget(['file' => 'boxes/text.tpl', 'params' => ['text' => $this->settings[$languages_id]['text']]]);
    }
  }
}