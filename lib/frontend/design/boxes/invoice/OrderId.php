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

class OrderId extends Widget
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
    \common\helpers\Translation::init('admin/design');

    $this->params["order"]->parent_id = $this->params["order"]->parent_id ?? null;
    if ($this->params["order"]->parent_id && $this->params["order"]->parent_id != $this->params["order"]->order_id) {
      $order = new \common\classes\Order($this->params["order"]->parent_id); /// 2check
    } else {
      $order = $this->params["order"];
    }
    $title = $num = '';
    if (empty($this->settings[0]['show_number']) || $this->settings[0]['show_number']==2) {
        $title .= ($this->settings[0]['show_text'] != 'no' ? TEXT_ORDER_ID : '');
        $num .= (method_exists($order, 'getOrderNumber')?$order->getOrderNumber():$order->order_id);
    }
    if (!empty($this->settings[0]['show_number']) && method_exists($order, 'getInvoiceNumber') ) {
        $tmp  = $order->getInvoiceNumber();
        if (!empty($tmp) && $tmp != $num) {
            if (!empty($num) && !empty($tmp)) {
                $num .= '<br />' ."\n ";
            }
            $num .= ($this->settings[0]['show_text'] != 'no' ? TEXT_INVOICE . ' ' : '');
            $num .= $tmp;
        }
    }

    return $title . ' ' . $num;
  }
}