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

namespace frontend\design\boxes\quote;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\CartDecorator;

class Products extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $quote, $languages_id;
        
        $quoteDecorator = new CartDecorator($quote);
        $quoteDecorator->setContorllerDispatch('quote-cart');
        $quoteDecorator->setRemoveAction('remove_quote');
        
        if (Yii::$app->controller->id == 'quote-checkout') {
            $this->type = 2;
        }

        if ($quote->count_contents() > 0) {
            return IncludeTpl::widget(['file' => 'boxes/quote/products' . ($this->type ? '-' . $this->type : '') . '.tpl', 'params' => [
              'products' => $quoteDecorator->getProducts(),
              'allow_checkout' => true,//!($quoteDecorator->oos_product_incart || $quoteDecorator->bound_quantity_ordered), - in previous version was false result for oos_product_incart || bound_quantity_ordered in any way
              'oos_product_inquote' => $quoteDecorator->oos_product_incart,
              'bound_quantity_ordered' => $quoteDecorator->bound_quantity_ordered,
            ]]);
        } else {
            return '<div class="empty">' . QUOTE_CART_EMPTY . '</div>';
        }
    }
}