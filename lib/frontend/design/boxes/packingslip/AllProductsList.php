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

class AllProductsList extends Widget
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
      
    if ($this->settings[0]['pdf']) {
      $width = Info::blockWidth($this->id);
      $html = '<h4>'.TEXT_SUMMARY_PRODUCTS_LISTING.'</h4>
      <table class="invoice-products" style="width: 100%" cellpadding="5">
  <tr class="invoice-products-headings">
    <td style="padding-left: 0; width: 5%; background-color: #eee; ">' . QTY . '</td>
    <td style="width:65%; background-color: #eee; ">' . TEXT_NAME . '</td>
    <td style="width:30%; background-color: #eee; ">' . TEXT_MODEL . '</td>
  </tr>';

      

      $products = $this->params['products'];

        $counter = 0;
      foreach ($products as $product) {
          if ((!$this->params['from'] && !$this->params['to']) || ($counter >= $this->params['from'] && $counter < $this->params['to'])) {
              $html .= '
      <tr>
        <td style=" border-top: 1px solid #ccc">' . $product['qty'] . '</td>
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
        return '';
    }
  }
}