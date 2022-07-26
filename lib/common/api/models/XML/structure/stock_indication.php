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

return [
    'Header' => 'site/stock_indication',
    'Data' => [
        'common\\models\\ProductsStockIndication' => [
            'xmlCollection' => 'ProductsStockIndications>StockIndication',
            'withRelated' => [
                'productsStockIndicationText' => [
                    'xmlCollection' => 'StockIndicationTexts>StockIndicationText',
                    'properties'=>[
                        'language_id' => ['class' => 'IOLanguageMap'],
                    ]
                ],
            ],
        ],
    ],
    'covered_tables' => [ 'products_stock_indication', 'products_stock_indication_text', ],
];