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
    'Header' => 'site/tax_zones',
    'dependsOn' => ['site/countries'],
    'Data' => [
        'common\\models\\TaxZones' => [
            'xmlCollection' => 'TaxZones>TaxZone',
            'withRelated' => [
                'zoneZones' => [
                    'xmlCollection' => 'Zones>Zone',
                    'properties' => [
                        'association_id' => false,
                        'zone_country_id' => ['class'=>'IOCountryMap'],
                        'zone_id' => ['class'=>'IOCountryZoneMap'],
                    ],
                ],
            ],
        ]
    ],
    'covered_tables' => [ 'tax_zones', 'zones_to_tax_zones' ],
];
