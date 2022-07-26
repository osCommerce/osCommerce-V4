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

class Menu extends Widget
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
    $menus = array();
    $sql = tep_db_query("select * from " . TABLE_MENUS);
    while ($row=tep_db_fetch_array($sql)){
      $menus[] = $row;
    }

    return $this->render('menu.tpl', [
      'id' => $this->id, 'params' => $this->params, 'menus' => $menus, 'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}