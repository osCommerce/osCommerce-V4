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

namespace backend\design;

use Yii;
use yii\base\Widget;

class SelectProducts extends Widget
{
    public $name;
    public $selectTitle;
    public $selectedName;
    public $selectedProducts;
    public $selectedPrefix;
    public $selectedSortName;
    public $selectedBackLink;
    public $selectedBackLink_c;
    public $onlyIncludeJs = false;

    public function init(){
        parent::init();
    }

    public function run()
    {
        $tr = \common\helpers\Translation::translationsForJs([
            'TEXT_ROOT', 'TEXT_ADD', 'IMAGE_BACK', 'TABLE_HEADING_PRICE_EXCLUDING_TAX',
            'TABLE_HEADING_PRICE_INCLUDING_TAX', 'TEXT_STOCK_QTY', 'TEXT_MODEL', 'TEXT_TYPE_CHOOSE_PRODUCT',
            'TEXT_PRODUCT_NOT_SELECTED', 'TEXT_IMG', 'TEXT_LABEL_NAME', 'TEXT_PRICE',
            'FIELDSET_ASSIGNED_PRODUCTS', 'SEARCH_BY_ATTR', 'TEXT_MODEL', 'TEXT_BACKLINK',
            'BATCH_BACK_LINK_TOOLTIP_TITLE', 'ADD_SELECTED_PRODUCTS', 'TEXT_ADDED',
        ], false);

        \backend\design\Data::addJsData(['tr' => $tr]);

        $selectedProducts = [];
        if (is_array($this->selectedProducts) ) {
            foreach ($this->selectedProducts as $product) {
                $selectedProducts[] = $product;
            }
        }

        return $this->render('select-products.tpl', [
            'name' => $this->name,
            'selectTitle' => $this->selectTitle,
            'selectedName' => $this->selectedName,
            'selectedProducts' => addslashes(json_encode($selectedProducts)),
            'selectedPrefix' => $this->selectedPrefix,
            'selectedSortName' => $this->selectedSortName,
            'selectedBackLink' => $this->selectedBackLink,
            'selectedBackLink_c' => $this->selectedBackLink_c,
            'onlyIncludeJs' => $this->onlyIncludeJs,
        ]);
    }
}