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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class InfoPage extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $languages_id;

    $pages = array();
    $query = tep_db_query("select * from " . TABLE_INFORMATION . " where languages_id = '" . $languages_id . "' and visible='1'");
    while ($item = tep_db_fetch_array($query)){
      $pages[] = $item;
    }

    return $this->render('info-page.tpl', [
      'id' => $this->id,
      'params' => $this->params,
      'settings' => $this->settings,
      'visibility' => $this->visibility,
      'pages' => $pages,
    ]);
  }
}