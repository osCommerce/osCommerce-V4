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

class InfoPage extends Widget
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


    if($this->settings[0]['info_page']) {

      $sql = tep_db_query("select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, if(length(i1.description), i1.description, i.description) as description, i.information_id from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' " . (\common\classes\platform::activeId() ? " AND i1.platform_id='" . \common\classes\platform::currentId() . "' " : '') . " where i.information_id = '" . (int)$this->settings[0]['info_page'] . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 " . (\common\classes\platform::activeId() ? " AND i.platform_id='" . \common\classes\platform::currentId() . "' " : ''));
      $row = tep_db_fetch_array($sql);

        $text =  \frontend\design\EditData::addEditDataTeg(stripslashes($row['description']), 'info', 'description', $this->settings[0]['info_page']);

      return IncludeTpl::widget(['file' => 'boxes/text.tpl', 'params' => ['text' => $text]]);
    } else {
      return '';
    }
  }
}