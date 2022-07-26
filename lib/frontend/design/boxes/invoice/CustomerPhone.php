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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class CustomerPhone extends Widget
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
    if (\common\helpers\Php8::getValue($this->params, 'order->customer.telephone')) {
      $heading = '';
      if ($this->settings[0]['show_heading'] ?? null) {
        $heading = '<div style="font-size:14px;font-weight:bold;padding-top:10px;text-transform:uppercase;">' . ENTRY_TELEPHONE_NUMBER . ':</div>';
      }
      return $heading . $this->params["order"]->customer['telephone'];
    } else {
      return '';
    }
  }
}