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

class BazaarvoiceReview extends \yii\base\Widget
{
    public $file;
    public $params;
    public $settings;

    public function run($uProductId = 0)
    {
        $params = \Yii::$app->request->get();
        if (!$params['products_id']) {
            if ((int)$uProductId > 0) {
                $params['products_id'] = trim($uProductId);
            } else {
                return '';
            }
        }
        $products = \Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);
        return \common\extensions\Bazaarvoice\Bazaarvoice::getHtmlReview($product);
    }
}