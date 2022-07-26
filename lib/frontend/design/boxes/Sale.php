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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Sale extends Widget
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
        $languages_id = \Yii::$app->settings->get('languages_id');

        $products_id = (int)$this->settings[0]['products_id'];

        if (!\common\helpers\Product::check_product($products_id, 1, true)) {
            return '';
        }
        $currencies = \Yii::$container->get('currencies');
        $product = tep_db_fetch_array(tep_db_query("
                  select
                    p.products_id,
                    p.products_price,
                    pd.products_name,
                    pd.products_description_short,
                    s.expires_date
                  from
                    " . TABLE_PRODUCTS . " p
                        left join  " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            on
                            pd.products_id = p.products_id and
                            pd.language_id = " . (int)$languages_id . " and
                            pd.platform_id = '" . Yii::$app->get('platform')->config(PLATFORM_ID)->getPlatformToDescription(). "'
                        left join  " . TABLE_SPECIALS . " s
                            on
                            s.products_id = p.products_id
                  where 
                    p.products_id = '" . $products_id . "' and 
                    s.status > 0"));
        if (!$product) {
            return '';
        }

        $days = $hours = $minutes = $seconds = 0;
        $expiresDate = 0;

        if (strtotime($product['expires_date'])) {
            $lastTime = strtotime($product['expires_date']) - date("U");

            if ($lastTime < 0) {
                return '';
            }

            $days = floor($lastTime / (3600 * 24));
            $lastTime = $lastTime - $days * (3600 * 24);
            $hours = floor($lastTime / 3600 );
            $lastTime = $lastTime - $hours * 3600;
            $minutes = floor($lastTime / 60);
            $lastTime = $lastTime - $minutes * 60;
            $seconds = $lastTime;

            $expiresDate = \common\helpers\Date::date_short($product['expires_date']);
        }

        $special_price = \common\helpers\Product::get_products_special_price($product['products_id']);
        $price = \common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']);

        $save = '';
        if ($price && $special_price) {
            $save = round((($price - $special_price) / $price) * 100);
        }

        $product['price_old'] = $currencies->display_price($price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));

        $product['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));

        $imageUrl = \common\classes\Images::getImageUrl($product['products_id'], 'Medium');

        $link = Yii::$app->urlManager->createAbsoluteUrl(['catalog/product', 'products_id' => $product['products_id']]);
        
        return IncludeTpl::widget(['file' => 'boxes/sale.tpl', 'params' => [
            'product' => $product,
            'expiresDate' => $expiresDate,
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'save' => $save,
            'imageUrl' => $imageUrl,
            'link' => $link,
            'id' => $this->id,
        ]]);
    }
}