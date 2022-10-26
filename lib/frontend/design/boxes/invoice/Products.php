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
use \backend\design\editor\Formatter;

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

  public function run()
  {
        $currencies = \Yii::$container->get('currencies');

        if ($this->settings[0]['pdf']) {
            $width = Info::blockWidth($this->id);
            $html = '
                <table class="invoice-products" style="width: 100%" cellpadding="5">
                <tr class="invoice-products-headings">
                    <td style="padding-left: 0; width: 6%; background-color: #eee; ">' . QTY . '</td>
                    <td style="width:34%; background-color: #eee; ">' . TEXT_NAME . '</td>
                    <td style="width:17%; background-color: #eee; ">' . TEXT_MODEL . '</td>
                    <td style="width:7%; background-color: #eee; ">' . TEXT_TAX . '</td>
                    <td style="text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_PRICE_EXCLUDING_TAX . '</td>
                    <td style="text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_TOTAL_EXCLUDING_TAX . '</td>
                    <td style="padding-right: 0;text-align:right;width:12%; background-color: #eee; ">' . TABLE_HEADING_TOTAL_INCLUDING_TAX . '</td>
                </tr>';

            $order = $this->params['order'];
            $counter = 0;
            $this->params['from'] = $this->params['from'] ?? null;
            $this->params['to'] = $this->params['to'] ?? null;
            if (is_array($order->products)) {
                foreach ($order->getOrderedProducts('invoice') as $product) {
                    if ((!$this->params['from'] && !$this->params['to']) || ($counter >= $this->params['from'] && $counter < $this->params['to'])) {

                        $html .= '
                            <tr>
                                <td style=" border-top: 1px solid #ccc">' . \common\helpers\Product::getVirtualItemQuantity($product['id'], $product['qty']) . '</td>
                                <td style=" border-top: 1px solid #ccc">' . $product['name'];
                        if (isset($product['tpl_attributes']) && !empty($product['tpl_attributes'])) {
                            $html .= '<div><small><i>' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;', "\n\t"), array('&nbsp;', '<b>', '</b>', '<br>', '<br>'), htmlspecialchars($product['tpl_attributes'])) . '</i></small></div>';
                        } else
                        if (is_array($product['attributes'] ?? null)) {
                            foreach ($product['attributes'] as $attribut) {
                                $html .= '
                  <div><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut['option'])) . ': ' . $attribut['value'] . '</i></small></div>';
                            }
                        }
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
                            $html .= $ext::renderOrderProductAsset($product['orders_products_id'], true);
                        }
                        $html .= '
                            </td>
                                <td style=" border-top: 1px solid #ccc">' . $product['model'] . '</td>
                                <td style=" border-top: 1px solid #ccc">' . \common\helpers\Tax::display_tax_value($product['tax']) . '%</td>

                                <td style="text-align:right; border-top: 1px solid #ccc">' . $currencies->format(($currencies->calculate_price_in_order($order->info, $product['final_price']) * \common\helpers\Product::getVirtualItemQuantityValue($product['id'])), true, ($order->info['invoice_currency']??$order->info['currency']), ($order->info['invoice_currency_value']??$order->info['currency_value'])) . '</td>
                                <td style="text-align:right; border-top: 1px solid #ccc">' . Formatter::priceEx((\common\helpers\Product::getVirtualItemQuantityValue($product['id']) * $product['final_price']), $product['tax'], $product['qty'], ($order->info['invoice_currency']??$order->info['currency']), ($order->info['invoice_currency_value']??$order->info['currency_value']))
. '</td>'
                            . '<td style="text-align:right; border-top: 1px solid #ccc"><b>' .  Formatter::price((\common\helpers\Product::getVirtualItemQuantityValue($product['id']) * $product['final_price']), $product['tax'], $product['qty'], ($order->info['invoice_currency']??$order->info['currency']), ($order->info['invoice_currency_value']??$order->info['currency_value'])) . '</b></td>
                            </tr>';
                    }
                    $counter++;
                }
            }
            if ($order instanceof \common\classes\Splinter){
    if ($order->isCreditNote() && is_array($order->mixed)){
        foreach($order->mixed as $mixdata){
            $html .= '
          <tr>
            <td style=" border-top: 1px solid #ccc"></td>
            <td style=" border-top: 1px solid #ccc">' . $mixdata['data'] . '</td>

            <td style=" border-top: 1px solid #ccc"></td>
            <td style=" border-top: 1px solid #ccc"></td>
            <td style="text-align:right; border-top: 1px solid #ccc">' . $currencies->format($mixdata['value_exc_vat'], true, $order->info['currency'], $order->info['currency_value']) . '</td>
            <td style="text-align:right; border-top: 1px solid #ccc">' . $currencies->format($mixdata['value_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</td>
            <td style="text-align:right; border-top: 1px solid #ccc"><b>' . $currencies->format($mixdata['value_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>
          </tr>
    ';
        }
    }
}

      $html .= '
</table>
';




      return $html;
    } else {
      return IncludeTpl::widget(['file' => 'boxes/invoice/products.tpl', 'params' => [
        'order' => $this->params['order'],
        'currencies' => $this->params['currencies'],
        'to_pdf' => ($_GET['to_pdf'] ? 1 : 0),
        'width' => Info::blockWidth($this->id)
      ]]);
    }
  }
}