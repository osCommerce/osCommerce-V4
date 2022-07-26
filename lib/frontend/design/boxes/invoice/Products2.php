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
use frontend\design\Info;

class Products2 extends Widget
{

  public $id;
  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $currencies = \Yii::$container->get('currencies');

    if ($this->settings[0]['pdf']){
      $width = Info::blockWidth($this->id);
      $html = '
      <table class="invoice-products" style="width: 100%" cellpadding="5">
        <tr class="invoice-products-headings">
          <td style="width:20%;"></td>
          <td style="width:55%;"></td>
          <td style="padding-right: 0;text-align:right;width:25%;"></td>
        </tr>';

      $order = $this->params['order'];
      $counter = 0;
if (is_array($order->products)){
    foreach ($order->getOrderedProducts('invoice') as $product) {
        if ((!$this->params['from'] && !$this->params['to']) || ($counter >= $this->params['from'] && $counter < $this->params['to'])) {

      $html .= '
          <tr>
            <td>' . \common\helpers\Product::getVirtualItemQuantity($product['id'], $product['qty']) . '&nbsp;x&nbsp;</td>
            <td><b>' . $product['name'];
      if (isset($product['tpl_attributes']) && !empty($product['tpl_attributes'])){
          $html .= '<div><small><i>' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;',"\n\t"), array('&nbsp;', '<b>', '</b>', '<br>','<br>'), htmlspecialchars($product['tpl_attributes'])) . '</i></small></div>';
      } else
      if (is_array($product['attributes'])){
        foreach ($product['attributes'] as $attribut){
          $html .= '
                  <div><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut['option'])) . ': ' . $attribut['value'] . '</i></small></div>';
        }
      }
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
        $html .= $ext::renderOrderProductAsset($product['orders_products_id'], true);
      }
      $html .= '
           </b></td> 
            <td style="text-align:right;">' /*. $currencies->format_clear($currencies->calculate_price_in_order($order->info, $product['final_price'], $product['tax'], 1/\common\helpers\Product::getVirtualItemQuantity($product['id'], 1)), true, $order->info['currency'], $order->info['currency_value']) . '<br>'*/
          . '<b>' . $currencies->format_clear($currencies->calculate_price_in_order($order->info, $product['final_price'], $product['tax'], $product['qty']), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>
          </tr>
    ';
        }
        $counter++;
    }
}
if ($order instanceof \common\classes\Splinter){
    if ($order->isCreditNote() && is_array($order->mixed)){
        foreach($order->mixed as $mixdata){
            $html .= '
          <tr>
            <td style=" border-top: 1px solid #ccc">' . $mixdata['data'] . '</td>
            <td style="text-align:right; border-top: 1px solid #ccc"><b>' . $currencies->format($mixdata['value_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>
          </tr>
    ';
        }
    }
}

      $html .= '
</table>
';




    } else {
      $html = IncludeTpl::widget(['file' => 'boxes/invoice/products.tpl', 'params' => [
        'order' => $this->params['order'],
        'currencies' => $this->params['currencies'],
        'to_pdf' => 0, //($_GET['to_pdf'] ? 1 : 0),
        'width' => Info::blockWidth($this->id)
      ]]);
    }
    return $html;
  }
}