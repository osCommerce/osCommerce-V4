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
    'Header' => 'site/warehouses',
    'Data' => [
        'common\\models\\Warehouses' => [
            'xmlCollection' => 'Warehouses>Warehouse',
            'withRelated' => [
                'addressBook' => [
                    'xmlCollection' => 'AddressBooks>AddressBook',
                    'properties' => [
                        'entry_country_id' => ['rename'=>'CountryId', 'class' => 'IOCountryMap'],
                    ],
                ],
                'openHours' => [
                    'xmlCollection' => 'OpenHours>OpenHour',
                    'properties' => [
                        'warehouses_open_hours_id' => false,
                    ],
                ],
            ],
        ],
    ],
    'covered_tables' => ['warehouses', 'warehouses_address_book', 'warehouses_open_hours', ],
];