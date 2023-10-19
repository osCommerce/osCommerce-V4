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
use yii\helpers\Html;
use yii\helpers\Inflector;
use \backend\design\editor\Formatter;

class StructuredProducts extends Widget {

    public $id;
    public $file;
    public $params;
    public $settings;
    private $fields = [];
    private $structure;
    private $html;
    private $order;
    private $width;

    public function init() {
        parent::init();

        $this->order = $this->params['order'];
        if (!is_object($this->order))
            throw new \Exception('Invalid order object');
        $this->structure = new \backend\design\boxes\invoice\StructuredProducts;
        if (!empty($this->settings[0]['sort_order'])) {
            $this->fields = explode(";", $this->settings[0]['sort_order']);
        } else {
            $this->fields = $this->structure->baseColumns;
        }
        if (is_array($this->fields)) {
            foreach ($this->fields as $key => $field) {
                if (empty($this->settings[0][$field])) {
                    unset($this->fields[$key]);
                }
            }
        }
        $this->_widths();
    }

    private function _widths(){
        if (is_array($this->fields) && count($this->fields)) {
            $undefined = 0;
            $defined = 0;
            foreach ($this->fields as $field) {
                if (isset($this->settings[0]["width_" . $field])){
                    $this->width[$field] = (float)$this->settings[0]["width_" . $field];
                    $defined += $this->width[$field];
                } else {
                    $this->width[$field] = false;
                    $undefined++;
                }
            }
            if ($undefined){
                $avg = number_format( (100 - $defined) / $undefined, 2);
                foreach ($this->fields as $field) {
                    if ($this->width[$field] === false){
                        $this->width[$field] = $avg;
                    }
                }
            }
        }
    }

    public function formHead() {
        if (is_array($this->fields) && count($this->fields)) {
            foreach ($this->fields as $field) {
                $pos = $this->settings[0]["position_" . $field] ?? "left";
                $width = $this->width[$field];
                $this->html .= $this->wrapTd($this->structure->getLabel($field), ['style' => "background-color: #eee;width:{$width}%;text-align:{$pos};"]);
            }
            $this->html = $this->wrapTr($this->html, ['class' => "invoice-products-headings"]);
        }
    }

    public function formBody() {
        $counter = 0;
        if (is_array($this->order->products)) {
            $rows = [];
            foreach ($this->order->getOrderedProducts('invoice') as $product) {
                $html = '';
                if ((!($this->params['from'] ?? false) && !($this->params['to'] ?? false)) || ($counter >= ($this->params['from']??0) && $counter < ($this->params['to'] ?? 0))) {
                    foreach ($this->fields as $field) {
                        $pos = $this->settings[0]["position_" . $field] ?? "left";
                        $key = $this->structure->getKey($field);
                        if (method_exists($this, 'get' . Inflector::id2camel($key, "_"))) {
                            $html .= $this->wrapTd($this->{'get' . Inflector::id2camel($key, "_")}($product), ['style' => "text-align:{$pos};"]);
                        } elseif (isset($product[$key])) {
                            $html .= $this->wrapTd($product[$key], ['style' => "text-align:{$pos};"]);
                        } else {
                            $html .= $this->wrapTd('');
                        }
                    }
                    $this->html .= $this->wrapTr($html);
                }
                $counter++; //I dont't know what for?
            }
        }
    }

    public function getTax($product) {
        return \common\helpers\Tax::display_tax_value($product['tax']) . '%';
    }

    public function getPriceIncTax($product) {
        return Formatter::price($product['final_price'], $product['tax'], 1, $this->order->info['currency'], $this->order->info['currency_value']);
    }

    public function getTotalIncTax($product) {
        return Html::tag('b', Formatter::price($product['final_price'], $product['tax'], $product['qty'], $this->order->info['currency'], $this->order->info['currency_value']));
    }

    public function getTotalExcTax($product) {
        return Formatter::priceEx($product['final_price'], $product['tax'], $product['qty'], $this->order->info['currency'], $this->order->info['currency_value']);
    }

    public function getName($product) {
        $name = $product['name'];
        if (isset($product['tpl_attributes']) && !empty($product['tpl_attributes'])) {
            $name .= '<div><small><i>' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;', "\n\t"), array('&nbsp;', '<b>', '</b>', '<br>', '<br>'), htmlspecialchars($product['tpl_attributes'])) . '</i></small></div>';
        } else
        if (is_array($product['attributes'])) {
            foreach ($product['attributes'] as $attribut) {
                $name .= '
      <div><small>&nbsp;<i> - ' . str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut['option'])) . ': ' . $attribut['value'] . '</i></small></div>';
            }
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            $name .= $ext::renderOrderProductAsset($product['orders_products_id']);
        }
        return $name;
    }

    private function getInfo($product, $field) {
        if (\common\helpers\Inventory::isInventory($product['id'])) {
            $inv = \common\models\Inventory::find()->select($field)->where(['products_id' => $product['id']])->one();
        }
        if (!$inv) {
            $inv = \common\models\Products::find()->where(['products_id' => (int)$product['id']])->one();
        }
        if ($inv) {
            return $inv->{$field};
        }
        return false;
    }

    public function getModelBarcode($product) {
        if (!empty($product['model'])) {
            return $this->drawBarcode($product['model'], [], ['style' => 'height:40px;width:200px;']);
        }
        return '';
    }

    public function getEan($product) {
        if ($ean = $this->getInfo($product, 'products_ean')) {
            return $ean;
        }
        return '';
    }

    public function getEanBarcode($product) {
        $ean = $this->getEan($product);
        if (!empty($ean)) {
            return $this->drawBarcode($ean, [], ['style' => 'height:40px;width:200px;']);
        }
        return '';
    }

    public function getUpc($product) {
        if ($upc = $this->getInfo($product, 'products_upc')) {
            return $upc;
        }
        return '';
    }

    public function getUpcBarcode($product) {
        $upc = $this->getUpc($product);
        if (!empty($upc)) {
            return $this->drawBarcode($upc, [], ['style' => 'height:40px;width:200px;']);
        }
        return '';
    }

    public function getAsin($product) {
        if ($asin = $this->getInfo($product, 'products_asin')) {
            return $asin;
        }
        return '';
    }

    public function getAsinBarcode($product) {
        $asin = $this->getAsin($product);
        if (!empty($asin)) {
            return $this->drawBarcode($asin, [], ['style' => 'height:40px;width:200px;']);
        }
        return '';
    }

    public function getIsbn($product) {
        if ($isbn = $this->getInfo($product, 'products_isbn')) {
            return $isbn;
        }
        return '';
    }

    public function getIsbnBarcode($product) {
        $isbn = $this->getIsbn($product);
        if (!empty($isbn)) {
            return $this->drawBarcode($isbn, [], ['style' => 'height:50px;width:200px;']);
        }
        return '';
    }

    public function drawBarcode($content, $barcodeOptions = [], $options = []) {
        $barcodeSize = ($barcodeOptions ? $barcodeOptions : [38, 7.5]);
        $barcodeobj = new \TCPDFBarcode($content, 'C128');
        $barcode = base64_encode($barcodeobj->getBarcodePngData($barcodeSize[0], $barcodeSize[1]));
        $options['src'] = "@$barcode";
        return Html::tag('img', '', $options);
    }

    public function wrapTable($html) {
        return Html::tag('table', $html, ['class' => 'invoice-products', 'style' => 'width: 100%', 'cellpadding' => 5]);
    }

    public function wrapTd($html, $options = []) {
        return Html::tag("td", $html, $options);
    }

    public function wrapTr($html, $options = []) {
        return Html::tag("tr", $html, $options);
    }

    public function run() {

        if (is_array($this->order->products)) {
            $width = Info::blockWidth($this->id);
            $this->formHead();
            $this->formBody();
            return $this->wrapTable($this->html);
        }
    }

}
