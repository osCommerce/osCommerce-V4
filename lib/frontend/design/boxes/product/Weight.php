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

class Weight extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function run()
    {
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }
        $products = Yii::$container->get('products');
        $data = $products->getProduct($params['products_id']);

        $weightKg = $data['weight_cm'] ?: $data['product_weight'];

        if ($data['model'] && \yii\helpers\ArrayHelper::getValue($this->settings, [0,'show_model']) != 'no') {
            \frontend\design\JsonLd::addData(['Product' => [
                'weight' => [
                    "@type" => "QuantitativeValue",
                    "@id" => "https://schema.org/QuantitativeValue",
                    "unitText" => "Kg",
                    "unitCode" => "KGM",
                    "value" => $weightKg,
                ]
            ]], ['Product', 'weight']);
        }


        $optWU = $this->settings[0]['display_weight'] ?? null;
        if ('no' === $optWU) return '';

        $weightUnit = defined('TEXT_WEIGHT_UNIT_KG') ? TEXT_WEIGHT_UNIT_KG : 'Kgs';
        $weight = round($weightKg, 2);
        if ($optWU == 'lb' || ($optWU == '' && defined('WEIGHT_UNIT_DEFAULT') && WEIGHT_UNIT_DEFAULT == 'LB')) {
            $weightUnit = defined('TEXT_WEIGHT_UNIT_LB') ? TEXT_WEIGHT_UNIT_LB : 'Lbs';
            $weight = round($data['weight_in'] ?: $weightKg * 2.20462262, 2);
        };

        $label = ($this->settings[0]['display_label'] ?? null) ? '<strong>'.WEIGHT.'</strong> ' : '';

        return '<div class="product-weight">' .$label. '<span itemprop="weight">' . $weight . '</span> <span class="product-weight-unit">' . $weightUnit . '</span></div>';
    }
}