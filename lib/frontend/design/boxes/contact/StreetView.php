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

namespace frontend\design\boxes\contact;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class StreetView extends Widget
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
    $data = Info::platformData();

    return IncludeTpl::widget(['file' => 'boxes/contact/street-view.tpl', 'params' => [
      'address' =>
        $data['street_address'] . ', ' .
        $data['suburb'] .($data['suburb'] ? ', ' : '') .
        $data['city'] . ', ' .
        $data['state'] . ', ' .
        $data['postcode'] . ', ' .
        $data['country'],
      'key' => \common\components\GoogleTools::instance()->getMapProvider()->getMapsKey()
    ]]);

  }
}