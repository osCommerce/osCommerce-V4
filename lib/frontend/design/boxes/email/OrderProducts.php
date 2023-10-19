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

namespace frontend\design\boxes\email;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class OrderProducts extends Widget
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

        if (defined("THEME_NAME")) {
            $theme_name = THEME_NAME;
        } elseif ($this->params['platform_id']) {
            $theme = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes p2t, themes t where p2t.theme_id = t.id  and p2t.platform_id = '" . $this->params['platform_id'] . "'"));
            $theme_name = $theme['theme_name'];
        } else {
            $theme_name = '';
        }

        $attributesArray = [];
        $attributesText = [];

        if ($theme_name) {
            $stylesByClasses = \backend\design\Style::getStylesByClasses($theme_name, '.b-email');
            $attributesArray = $stylesByClasses['attributesArray'];
            $attributesText = $stylesByClasses['attributesText'];
        }

        $rows = '';

        $showAssets = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed');

        $price = false;
        $qty = false;
        foreach ($this->params['products'] as $item) {
            $item['orders_products_id'] = 0;
            if (isset($this->params['order']) AND ($this->params['order'] instanceof \common\classes\Order)) {
                $item['orders_products_id'] = (int)$this->params['order']->searchOrderProduct($item);
            }
            \common\helpers\Php8::nullArrProps($item, ['tpl_price', 'tpl_attributes', 'qty', 'model', 'name']);
            $rows .= '
          <tr class="product-row" style="' . ($attributesText['.product-row'] ?? '') . '">
            ' . ( ($attributesArray['.image']['display'] ?? '') != 'none' ? '
            <td class="image" style="' . ($attributesText['.image'] ?? '') . (($item['parent_product']??false) ? ($attributesText['.subitem'] ?? '') : '') . '">
              <img src="' . \common\classes\Images::getImageUrl($item['id'], 'Thumbnail', -1, 0, false, (defined('USE_WEBP_IN_EMAILS') && USE_WEBP_IN_EMAILS=='True')) . '" alt="" style="' . ($attributesText['img'] ?? '') . '">
            </td>
            ' : '') . '
            ' . (($attributesArray['.name']['display'] ?? '') != 'none' ? '
            <td class="name" style="' . ($attributesText['.name'] ?? '') . '">' . $item['name'] . $item['tpl_attributes'] . " ". ($showAssets? $showAssets::renderOrderProductAsset($item['orders_products_id'], true):'') . '</td>
            ' : '') . '
            ' . (($attributesArray['.model']['display'] ?? '') != 'none' ? '
            <td class="model" style="' . ($attributesText['.model'] ?? '') . '">' . $item['model'] . '</td>
            ' : '') . '
            ' . ((($attributesArray['.qty']['display'] ?? '') != 'none' && $item['qty']) ? '
            <td class="qty" style="' . ($attributesText['.qty'] ?? '') . '">' . $item['qty'] . '</td>
            ' : '') . '
            ' . ((($attributesArray['.price']['display'] ?? '') != 'none' && $item['tpl_price']) ? '
            <td class="price" style="' . ($attributesText['.price'] ?? '') . '">' . $item['tpl_price'] . '</td>
            ' : '') . '
        </tr>
          ';

            if ($item['qty'] && ($attributesArray['.qty']['display'] ?? '') != 'none') {
                $qty = true;
            }
            if ($item['tpl_price'] && ($attributesArray['.price']['display'] ?? '') != 'none') {
                $price = true;
            }
        }

        $headings = '';
        if (($attributesArray['.heading-products']['display'] ?? '') != 'none') {
            $headings = '
    <tr class="heading-products" style="' . ($attributesText['.heading-products'] ?? '') . '">
        ' . (($attributesArray['.heading-image']['display'] ?? '') != 'none' ? '
        <td class="heading-image" style="' . ($attributesText['.heading-image'] ?? '') . '"></td>
        ' : '') . '
        ' . (($attributesArray['.heading-name']['display'] ?? '') != 'none' ? '
        <td class="heading-name" style="' . ($attributesText['.heading-name'] ?? '') . '">' . TEXT_NAME_PERSONAL . '</td>
        ' : '') . '
        ' . (($attributesArray['.heading-model']['display'] ?? '') != 'none' ? '
        <td class="heading-model" style="' . ($attributesText['.heading-model'] ?? '') . '">' . TEXT_MODEL . '</td>
        ' : '') . '
        ' . ((($attributesArray['.heading-qty']['display'] ?? '') != 'none' && $qty) ? '
        <td class="heading-qty" style="' . ($attributesText['.heading-qty'] ?? '') . '">' . QTY . '</td>
        ' : '') . '
        ' . ((($attributesArray['.heading-price']['display'] ?? '') != 'none' && $price) ? '
        <td class="heading-price" style="' . ($attributesText['.heading-price'] ?? '') . '">' . TABLE_HEADING_TOTAL_INCLUDING_TAX . '</td>
        ' : '') . '
    </tr>';
        }

        $html = '
  <table class="products-table" ' . (isset($attributesText['.products-table']) ? ' style="' . $attributesText['.products-table'] . '"' : '') . ' cellpadding="0" cellspacing="0" border="0" width="100%">'
            . $headings
            . $rows
            . '</table>';

        return $html;
    }
}