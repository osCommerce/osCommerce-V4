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
use \common\helpers\Php8;
use frontend\design\IncludeTpl;

class AddressQrcode extends Widget
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
    $address = \common\helpers\Address::address_format(Php8::getValue($this->params, 'order->delivery.format_id'), Php8::getValue($this->params, 'order->delivery'), 0, '', "\n");
    if (!empty($address) && !empty(Php8::getValue($this->params, 'order->customers.id'))){
        global $request_type;
        $action = 'account/order-qrcode';
        if ('quote_' == \common\helpers\Php8::getValue($this->params, 'order.table_prefix'))
            $action = 'account/quotation-qrcode';
        return '<img alt="' . \common\helpers\Output::output_string($address) . '" src="' . ($request_type=='SSL'?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_CATALOG . $action.'?oID=' . $this->params["oID"] . '&cID=' . $this->params["order"]->customer['id'] . '">';
    }
  }
}