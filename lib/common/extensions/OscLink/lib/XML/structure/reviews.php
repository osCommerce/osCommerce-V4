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
    'Header' => 'site/reviews',
    'dependsOn' => ['site/products', 'site/customers'],
    'Data' => [
        'common\\models\\Reviews' => [
            'xmlCollection' => 'Reviews>Review',
            'properties' => [
                'products_id' => ['class'=>'IOMap', 'table'=>'products', 'attribute'=>'products_id'],
                'customers_id' => ['class'=>'IOMap', 'table'=>'customers', 'attribute'=>'customers_id'],
            ],
            'withRelated' => [
                'descriptions' => [
                    'xmlCollection' => 'Descriptions>Description',
                    'properties' => [
                        'languages_id' => ['class' => 'IOLanguageMap'],
                    ],
                ],
            ],
         ]
    ],
    'covered_tables' => [ 'reviews', 'reviews_description', ],
];
