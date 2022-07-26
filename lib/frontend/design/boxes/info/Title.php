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
use frontend\design\Info;

class Title extends Widget
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
    $full_action = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

    $info_id = (int)Yii::$app->request->get('info_id', 0);
    if(!$info_id && $full_action!='catalog/gift-card') return '';

    if ($full_action=='catalog/gift-card') {
        $h1 = defined('HEAD_H1_TAG_GIFT_CARD') && tep_not_null(HEAD_H1_TAG_GIFT_CARD) ? HEAD_H1_TAG_GIFT_CARD : '';
        $title = TEXT_GIFT_CARD;
    } else {
        $row = InformationPage::getFrontendDataVisible((int)$info_id);
        $title = '';
        if (isset($row['info_title']) && $row['info_title'] != '') {
          $title = \frontend\design\EditData::addEditDataTeg(stripslashes($row['info_title']), 'info', 'info_title', $info_id);
        } elseif (isset($row['page_title']) && $row['page_title'] != '') {
          $title =  \frontend\design\EditData::addEditDataTeg(stripslashes($row['page_title']), 'info', 'page_title', $info_id);
        }
        $h1 = $row['information_h1_tag'];
        $h1 =  \frontend\design\EditData::addEditDataTeg($h1, 'info', 'information_h1_tag', $info_id);
    }
    $this->settings[0]['show_heading'] = (isset($this->settings[0]['show_heading']) ? $this->settings[0]['show_heading'] : false);
    return IncludeTpl::widget(['file' => 'boxes/info/title.tpl', 'params' => [
        'title' => $title,
        'h1' => $h1,
        'settings' => $this->settings,
    ]]);
  }
}