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

namespace backend\design\boxes\invoice;

use Yii;
use yii\base\Widget;

class StructuredProducts extends Widget {

    public $id;
    public $params;
    public $settings;
    public $visibility;
    public $baseColumns = [];
    public $extendedColumns = [];

    public function init() {
        parent::init();
        \common\helpers\Translation::init('invoice');
        \common\helpers\Translation::init('admin/main');
        $this->baseColumns = [
            'column_qty' => ENTRY_INVOICE_QTY,
            'column_name' => TEXT_NAME,
            'column_model' => TEXT_MODEL,
            'column_tax' => TABLE_HEADING_TAX,
            'column_price_inc_tax' => TABLE_HEADING_PRICE_INCLUDING_TAX,
            'column_total_exc_tax' => TABLE_HEADING_TOTAL_EXCLUDING_TAX,
            'column_total_inc_tax' => TABLE_HEADING_TOTAL_INCLUDING_TAX,
        ];
        $this->extendedColumns = [
            'column_ean' => "strtoupper",
            'column_upc' => "strtoupper",
            'column_asin' => "strtoupper",
            'column_isbn' => "strtoupper",
            'column_model_barcode' => TEXT_MODEL . ' ' . TEXT_BARCODE,
            'column_ean_barcode' => 'EAN ' . TEXT_BARCODE,
            'column_upc_barcode' => 'UPC ' . TEXT_BARCODE,
            'column_asin_barcode' => 'ASIN ' . TEXT_BARCODE,
            'column_isbn_barcode' => 'ISBN ' . TEXT_BARCODE,
        ];
    }

    public function run() {

        return $this->render('../../views/invoice/structured-products.tpl', [
                    'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings,
                    'visibility' => $this->visibility,
                    'attribute' => $this
        ]);
    }

    public function getKey($subject) {
        return preg_replace("/column_/", "", $subject);
    }

    public function getLabel($subject) {
        if (isset($this->extendedColumns[$subject])) {
            if (is_callable($this->extendedColumns[$subject])) {
                return call_user_func($this->extendedColumns[$subject], $this->getKey($subject));
            } else {
                return $this->extendedColumns[$subject];
            }
        }

        if (isset($this->baseColumns[$subject]) && !empty($this->baseColumns[$subject])) {
            return $this->baseColumns[$subject];
        }

        $subject = $this->getKey($subject);
        return (defined("TEXT_" . strtoupper($subject)) ? constant("TEXT_" . strtoupper($subject)) : \yii\helpers\Inflector::humanize($subject, "_"));
    }

    public function getMoreCoulmns() {
        $columns = array_keys($this->baseColumns);
        $list = array_diff(array_keys($this->extendedColumns), $columns);
        $attr = $this;
        return array_combine($list, array_map(function($item) use ($attr) {
                    return $this->getLabel($item);
                }, $list));
    }

    public function getDisabledColumns() {
        if ($this->settings[0]['sort_order']) {
            $columns = explode(";", $this->settings[0]['sort_order']);
        } else {
            $columns = array_keys($this->baseColumns);
        }
        $list = array_intersect($columns, array_keys($this->extendedColumns));
        return array_fill_keys($list, ['disabled' => true]);
    }

}
