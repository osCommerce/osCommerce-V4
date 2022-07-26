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
    'Header' => 'site/taxes',
    'dependsOn' => ['site/tax_zones'],
    'Data' => [
        'common\\models\\TaxClass' => [
            'xmlCollection' => 'TaxClasses>TaxClass',
            'withRelated' => [
                'taxRateList' => [
                    'xmlCollection' => 'TaxRates>TaxRate',
                    'properties' =>[
                        'tax_zone_id' => ['class'=>'IOMap', 'table'=>'tax_zones', 'attribute'=>'geo_zone_id'],
                    ]
                ],
            ],
         ]
    ],
    'covered_tables' => [ 'tax_class', 'tax_rates', ],
];
