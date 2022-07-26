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

class Video extends Widget
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

    $poster = \frontend\design\Info::themeImage($this->settings[$languages_id]['poster'], false, false);
    if ($poster) {
      $this->settings[$languages_id]['poster'] = Yii::getAlias('@web') . '/' . $poster;
    }
    $this->settings[$languages_id]['video'] = 
        Yii::getAlias('@web') . '/' .
        \frontend\design\Info::themeImage($this->settings[$languages_id]['video'], false, false);
    
    return IncludeTpl::widget([
      'file' => 'boxes/video.tpl',
      'params' => [
        'languages_id' => $languages_id,
        'settings' => $this->settings,
      ],
    ]);
  }
}