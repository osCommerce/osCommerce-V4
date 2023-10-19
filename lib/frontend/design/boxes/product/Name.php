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

class Name extends Widget
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
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }
        $name2 = null;
        $products = Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);

        if ($product['products_h1_tag']) {
            $name = $product['products_h1_tag'];
            $name2 = $product['products_name'];
        } else {
            $name = $product['products_name'];
        }

        Yii::$app->getView()->registerMetaTag([
            'property' => 'og:title',
            'content' => $name
        ],'og:title');

        \frontend\design\JsonLd::addData(['Product' => [
            'name' => $name
        ]], ['Product', 'name']);
        if ($name2 && $name != $name2) {
            \frontend\design\JsonLd::addData(['Product' => [
                'alternateName' => $name2
            ]], ['Product', 'alternateName']);
        }
        return IncludeTpl::widget(['file' => 'boxes/product/name.tpl', 'params' => [
            'name' => \common\helpers\Html::fixHtmlTags($product['products_name']),
            'h1' => \common\helpers\Html::fixHtmlTags($product['products_h1_tag']),
            'params'=> $this->params,
            'settings'=> $this->settings,
            'productUrl' => Yii::$app->urlManager->createAbsoluteUrl([
                'catalog/product', 'products_id' => $params['products_id']])
        ]]);
    }
}