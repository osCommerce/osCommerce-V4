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

class YoutubeVideo extends Widget
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
    if (strrpos($this->settings[$languages_id]['youtube_video'], 'youtu.be')) {
        $youtube_video = preg_match_all("/\/([^\/^?]+)/", $this->settings[$languages_id]['youtube_video'], $arr);
        $item['code'] = $arr[1][1];
    } elseif (strrpos($this->settings[$languages_id]['youtube_video'], 'youtube.com')) {
        $youtube_video = preg_match_all("/\/([^\/^?^\"]+)[\"\?]/", $this->settings[$languages_id]['youtube_video'], $arr);        
        $item['code'] = $arr[1][0];
    } else{
        $youtube_video = $this->settings[$languages_id]['youtube_video'];
    }

    return IncludeTpl::widget([
      'file' => 'boxes/youtube-video.tpl',
      'params' => [
        'languages_id' => $languages_id,
        'settings' => $this->settings,
        'youtube_video' => $item['code']
      ],
    ]);
  }
}