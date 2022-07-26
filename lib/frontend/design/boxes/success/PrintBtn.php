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

namespace frontend\design\boxes\success;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PrintBtn extends Widget
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
    if ($this->params['print_order_href']) {
      return IncludeTpl::widget(['file' => 'boxes/success/print-btn.tpl', 'params' => ['link' => $this->params['print_order_href']]]);
    } else {
      return '';
    }
  }
}