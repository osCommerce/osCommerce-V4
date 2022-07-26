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

class Image extends Widget
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

    $image = \frontend\design\Info::themeImage($this->settings[$languages_id]['logo'],
      [$this->settings[\common\classes\language::defaultId()]['logo'], $this->settings[0]['params']]);
    
    if ($this->settings[0]['pdf']){

        if (function_exists("tep_catalog_href_link")) {
            return '<img src="' . tep_catalog_href_link('images/' . $image) . '">';
        } else {
            return '<img src="' . Yii::$app->urlManager->createAbsoluteUrl('images/' . $image) . '">';
        }


    } else {

      return IncludeTpl::widget(['file' => 'boxes/image.tpl', 'params' => [
        'image' => BASE_URL . $image,
        'link' => $this->settings[$languages_id]['img_link']
      ]]);

    }
  }
}