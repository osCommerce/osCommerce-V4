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

use common\classes\Migration;

/**
 * Class m230510_141915_add_placeholder
 */
class m230510_141915_add_placeholder extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $placeholders = [
            '74331'           => 'header-content',
            '75681'           => 'header-content',
            '117212'          => 'header-content',
            '1.0E-6'          => 'header-content',
            '74332'           => 'header-content-left',
            '74338'           => 'header-content-small',
            '117219'          => 'header-content-small',
            '74339'           => 'header-content-small-left',
            '117220'          => 'header-content-small-left',

            '74256'           => 'footer-content',
            '75625'           => 'footer-content',
            '117078'          => 'footer-content',
            '1641484988'      => 'footer-content',

            '74508'           => 'main-content',
            '75747'           => 'main-content',
            '117386'          => 'main-content',
            '1641570125'      => 'main-content',

            '73462'           => 'catalog-content',
            '74785'           => 'catalog-content',
            '74553'           => 'catalog-content',
            '75857'           => 'catalog-content',
            '75588'           => 'catalog-content',
            '116272'          => 'catalog-content',
            '117678'          => 'catalog-content',
            '116309'          => 'catalog-content',
            '117447'          => 'catalog-content',

            '73463'           => 'catalog-side',
            '74786'           => 'catalog-side',
            '1677252154.6259' => 'catalog-side',
            '1677252302.9021' => 'catalog-side',
            '117679'          => 'catalog-side',

            '73472'           => 'catalog-features',
            '74790'           => 'catalog-features',
            '73486'           => 'catalog-features',
            '74561'           => 'catalog-features',
            '75585'           => 'catalog-features',
            '75862'           => 'catalog-features',
            '116282'          => 'catalog-features',
            '116312'          => 'catalog-features',
            '117454'          => 'catalog-features',
            '117683'          => 'catalog-features',

            '117906.000001'   => 'listing-item',
            '117665.000001'   => 'listing-item',
            '117665'          => 'listing-item',
            '117906'          => 'listing-item',
            '74771'           => 'listing-item',
            '73409'           => 'listing-item',
            '75024'           => 'listing-item',
            '75888'           => 'listing-item',
            '94970'           => 'listing-item',
            '114990'          => 'listing-item',
            '120919.000001'   => 'listing-item',
            '117918'          => 'listing-item',

            '117614'          => 'product-content',
            '74720'           => 'product-content',
            '120988'          => 'product-side',
            '74727'           => 'product-side',
            '1641826091'      => 'product-side',

            //'1677251583.4398' => 'product-image',
            '74722'           => 'product-image',
            '117616'          => 'product-image',
            '120998'          => 'product-image',

            '116218'          => 'cart-content',
            '115912'          => 'cart-content',

            '116236'          => 'cart-side',
            '209252'          => 'cart-side',

            '116326'          => 'checkout-content',
            '73500'           => 'checkout-content',
            '113723'          => 'checkout-content',

            '116362'          => 'checkout-side',
            '73536'           => 'checkout-side',
            '113747'          => 'checkout-side',

            '73510'          => 'checkout-shipping',
            '116336'         => 'checkout-shipping',

            '73528'          => 'checkout-payment',
            '116354'         => 'checkout-payment',

            '117967'          => 'info-content',
            '118003'          => 'info-content',
            '117224.000002'   => 'info-content',
            '116966'          => 'info-content',
            '117224'          => 'info-content',
            '117696'          => 'info-content',
            '117224.000001'   => 'info-content',
            '117793'          => 'info-content',
            '74346'           => 'info-content',

            '209144'          => 'account-side',
            '94936'           => 'account-side',
            '116127'          => 'account-side',
        ];

        foreach ($placeholders as $microtime => $placeholder) {
            $this->update('design_boxes', ['widget_params' => $placeholder], ['microtime' => $microtime]);
            $this->update('design_boxes_tmp', ['widget_params' => $placeholder], ['microtime' => $microtime]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
