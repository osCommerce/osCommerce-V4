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

namespace frontend\design\boxes\pdf;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class ProductElement extends Widget
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

        static $productsItem = 0;
        static $productsElements = [];
        static $productsItemSize = 0;
        static $productsElementsSize = [];
        if ($this->settings['item_clear'] ?? null){
            $productsItem = 0;
            $productsElements = [];
            $productsItemSize = 0;
            $productsElementsSize = [];
            return '';
        }

        $element = $this->settings[0]['product_element'];

        $products = $this->params['products'];

        if (!($this->settings[0]['pdf'] ?? null) && !is_array($products)) {
            return $element;
        }

        if (!is_array($products)) {
            return '';
        }

        if ($this->settings['out'] ?? null){// main boxes

            if ($productsElements[$element] ?? null) {
                $productsElements = [];
                $productsElements[$element] = 1;
                $productsItem++;
            } else {
                $productsElements[$element] = 1;
            }

            if ($productsItem > count($products) - 2) {
                \frontend\design\Info::$pdfProductsEnd = true;
            }
            return $products[$productsItem][$element] ?? null;

        } else {// boxes for counting size, it queried before show main boxes

            if ($productsElementsSize[$element] ?? null) {
                $productsElementsSize = [];
                $productsElementsSize[$element] = 1;
                $productsItemSize++;
            } else {
                $productsElementsSize[$element] = 1;
            }

            if (\frontend\design\Info::$pdfProductsEnd == true) {
                return '';
            }

            return $products[$productsItemSize][$element] ?? null;
        }
    }
}