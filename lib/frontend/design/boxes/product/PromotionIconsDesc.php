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

class PromotionIconsDesc extends Widget
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
        if (!\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            return '';
        }
        $products_id = Yii::$app->request->get('products_id', false);

        if ($this->params['product']) {
            if ($this->params['product']['products_id']){//listing
                $product = Yii::$container->get('products')->getProduct($this->params['product']['products_id']);
            } elseif ($this->params['product']['id']){//cart
                $product = Yii::$container->get('products')->getProduct($this->params['product']['id']);
            }
        }else if ($products_id) {
            $product = Yii::$container->get('products')->getProduct($products_id);
        } else {
            return '';
        }

        if (!(is_array($product['promo_details']) && count($product['promo_details']))) {
            //return '';
        }
        if ( isset($product['promo_details']) && is_array($product['promo_details']) ) {
            $promo_details = $product['promo_details'];
            if (is_array($promo_details) && !\common\models\promotions\PromotionService::applyProductSortOrder($products_id, $promo_details)) {
                uasort($promo_details, function ($a, $b) {
                    return $a['priority'] > $b['priority'] ? 1 : -1;
                });
            }
            $product->attachDetails(['promo_details' => $promo_details]);
        }

        return IncludeTpl::widget([
            'file' => 'boxes/product/promotion-icons-desc.tpl',
            'params' => [
                'product' => $product,
            ]
        ]);
    }
}