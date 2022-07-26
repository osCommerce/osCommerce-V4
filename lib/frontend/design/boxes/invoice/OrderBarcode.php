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

class OrderBarcode extends Widget
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
    global $request_type;
    $type = ''; // default is order not required
    if (!empty($this->params['order']) && is_object($this->params['order'])) {
      $reflect = new \ReflectionClass($this->params['order']);
      if ($reflect->getShortName() === 'Quotation') {
        $type = '&type=QuoteOrders';
      } elseif ($reflect->getShortName() === 'Sample') {
        $type = '&type=SampleOrders';
      }
    }
    
    $content = @file_get_contents(($request_type=='SSL'?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_CATALOG . 'account/order-barcode?oID=' . $this->params["oID"] . '&cID=' . $this->params["order"]->customer['id'] . $type);
    if ($content) {
        return '<img src="@' . base64_encode($content) . '">';
    }
    
  }
}