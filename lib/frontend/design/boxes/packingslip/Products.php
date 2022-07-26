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

namespace frontend\design\boxes\packingslip;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Products extends Widget
{

  public $id;
  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run() {

    if ($this->settings[0]['pdf']){
      $width = Info::blockWidth($this->id);
      $html = '
      <table class="invoice-products" style="width: 100%" cellpadding="5">
  <tr class="invoice-products-headings">
    <td style="padding-left: 0; width: 5%; background-color: #eee; ">' . QTY . '</td>
    <td style="width:65%; background-color: #eee; ">' . TEXT_NAME . '</td>
    <td style="width:30%; background-color: #eee; ">' . TEXT_MODEL . '</td>
  </tr>';

      $order = $this->params['order'];

        $counter = 0;
        \common\helpers\Php8::nullArrProps($this->params, ['from', 'to']);
      foreach ($order->getOrderedProducts('packing_slip') as $product) {
          if ((!$this->params['from'] && !$this->params['to']) || ($counter >= $this->params['from'] && $counter < $this->params['to'])) {
              $html .= '
      <tr>
        <td style=" border-top: 1px solid #ccc">' . \common\helpers\Product::getVirtualItemQuantity($product['id'], $product['qty']) . '</td>
        <td style=" border-top: 1px solid #ccc">' . $product['name'];

              if (!empty($product['attributes']) && is_array($product['attributes'])) {
                  foreach ($product['attributes'] as $attribut) {
                      $html .= '
              <div><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut['option'])) . ': ' . $attribut['value'] . '</i></small></div>';
                  }
              }
              $html .= '
        </td>

        <td style=" border-top: 1px solid #ccc">' . $product['model'] . '</td>
      </tr>
';
          }
          $counter++;
      }
      $html .= '
</table>
';




      return $html;
    } else {
      return IncludeTpl::widget(['file' => 'boxes/packingslip/products.tpl', 'params' => [
        'order' => $this->params['order'],
        'currencies' => $this->params['currencies'],
        'to_pdf' => ($_GET['to_pdf'] ? 1 : 0),
        'width' => Info::blockWidth($this->id)
      ]]);
    }
  }
}