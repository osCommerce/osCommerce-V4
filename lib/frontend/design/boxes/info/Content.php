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

namespace frontend\design\boxes\info;

use common\components\InformationPage;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Content extends Widget
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

    if(!$_GET['info_id']) return '';

    $info_id = (int)$_GET['info_id'];

    $row = InformationPage::getFrontendDataVisible((int)$info_id);
/*
in PHP:
        use frontend\design\boxes\Menu;
        Menu::widget([settings => [['params' => 'categories']]])

in content:
        <widget name="Menu" settings="{'params':'Categories'}"></widget>
*/

    $arr = explode('<widget ', $row['description']);
    $html = '';

    foreach ($arr as $item){
      if(stripos($item, '</widget>') !== false){

        $split = explode('</widget>', $item);

        preg_match("/name=\"([^\"]+)\"/", $split[0], $matches);
        $name = $matches[1];

        $settings = array();
        preg_match("/settings=\"([^\"]+)\"/", $split[0], $matches);
        $settings[0] = (array)json_decode( str_replace('\'', '"', $matches[1]));

        $widget_name = 'frontend\design\boxes\\' . $name;
        $html .= $widget_name::widget(['settings' => $settings]);

        $html .= $split[1];

      } else {
        $html .= $item;
      }
    }

      $html = \common\classes\TlUrl::replaceUrl($html);
      $html = \common\classes\PageComponents::addComponents($html);
      $html =  \frontend\design\EditData::addEditDataTeg(stripslashes($html), 'info', 'description', $info_id);

    return IncludeTpl::widget(['file' => 'boxes/info/content.tpl', 'params' => ['content' => $html ]]);
  }
}