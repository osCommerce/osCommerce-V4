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


return array(
    'Header' => 'site/quotes',
    'dependsOn' => ['site/languages', 'site/order_statuses', 'site/currencies', 'site/platforms', 'site/customers', 'site/products', 'site/countries'],
    'Data' => array(
        'common\\models\\QuoteOrders' => array(
            'xmlCollection' => 'QuoteOrders>QuoteOrder',
            'properties' => [
                'orders_status' => ['class'=>'IOOrderStatus'],
                'language_id' => ['class'=>'IOLanguageMap'],
            ],
            'withRelated' => array(
                'quoteOrdersProducts' => array(
                    'xmlCollection' => 'QuoteOrderProducts>QuoteOrderProduct',
                    'withRelated' => array(
                        'quoteOrderProductAttributes' => array(
                            'xmlCollection' => 'Attributes>Attribute',
                        )
                    ),
                ),
                'quoteOrdersTotals' => array(
                    'xmlCollection' => 'QuoteOrderTotals>QuoteOrderTotal',
                ),
                'quoteOrdersStatusHistory' => array(
                    'xmlCollection' => 'QuoteOrderStatusHistory>QuoteOrderStatus',
                    'properties' => [
                        'orders_status_id' => ['class'=>'IOOrderStatus'],
                    ]
                )
            ),
        ),
    ),
    'covered_tables' => array('quote_orders', 'quote_orders_products', 'quote_orders_products_attributes', 'quote_orders_total', 'quote_orders_status_history'),
);