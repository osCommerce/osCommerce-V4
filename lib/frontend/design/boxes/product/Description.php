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

class Description extends Widget
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
        
        $products = Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);

        if ($product['products_description_short']) {
            $ogDescription = $product['products_description_short'];
        } elseif ($product['products_description']) {
            $ogDescription = $product['products_description'];
        }

        if (isset($ogDescription) && !empty($ogDescription)) {
            $ogDescription = strip_tags($ogDescription);
            $ogDescription = str_replace("\n", '', $ogDescription);
            $ogDescription = str_replace("\t", ' ', $ogDescription);
            $ogDescription = trim($ogDescription);
            if (strlen($ogDescription) > 295) {
                $ogDescription = mb_substr($ogDescription, 0, 291) . '...';
            }
            Yii::$app->getView()->registerMetaTag([
                'property' => 'og:description',
                'content' => $ogDescription
            ],'og:description');


            \frontend\design\JsonLd::addData(['Product' => [
                'description' => $ogDescription
            ]], ['Product', 'description']);
        }

        if (!$product['products_description']) {
            return '';
        }
        $description = $product['products_description'];
        $description = \common\classes\TlUrl::replaceUrl($description);
        $description = \frontend\design\Info::widgetToContent($description);

        return IncludeTpl::widget(['file' => 'boxes/product/description.tpl', 'params' => ['description' => $description]]);

    }
}