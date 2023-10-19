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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class ButtonsQuote extends Widget
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
        if (!\common\helpers\Extensions::isAllowed('Quotations')) return '';

        $params = Yii::$app->request->get();

        if (!$params['products_id'] || GROUPS_DISABLE_CART) {
            return '';
        }
        
        if (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')) {
            return '';
        }

        $products = Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);
        $stock_info = $product[$products::TYPE_STOCK];

        if (!$stock_info['flags']['request_for_quote']) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/button-quote.tpl', 'params' => [
            'product_has_attributes' => \common\helpers\Attributes::has_product_attributes($params['products_id']),
            'customer_is_logged' => !Yii::$app->user->isGuest,

        ]]);
    }
}