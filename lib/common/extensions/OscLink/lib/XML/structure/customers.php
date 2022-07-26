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
    'Header' => [
        'type' => 'site/customers',
        //'version' => '1.0',
    ],
    'dependsOn' => ['site/languages', 'site/currencies', 'site/platforms', 'site/countries'],
    'Data' => [
        'common\\models\\Customers' => [
            'xmlCollection' => 'Customers>Customer',
            'properties' => [
                'customers_default_address_id' => ['class'=>'IOMap', 'table'=>'address_book','attribute'=>'address_book_id'],
                'groups_id' => ['class'=>'IOMap', 'table'=>'groups','attribute'=>'groups_id'],
                'customers_currency_id' => ['class' => 'IOCurrencyMap'],
                'platform_id' => ['class' => 'IOPlatformMap']
            ],
            'withRelated' => [
                'addressBooks' => [
                    'xmlCollection' => 'AddressBooks>AddressBook',
                    'properties' => [
                        //'groups_discounts_id' => false,
                        'entry_country_id' => ['class' => 'IOCountryMap'],
                    ],
                ],
                'customersEmails' => [
                    'xmlCollection' => 'CustomersEmails>CustomersEmail',
                ],
                'customersPhones' => [
                    'xmlCollection' => 'CustomersPhones>CustomersPhone',
                ],
                'customersInfo' => [
                    'xmlCollection' => 'CustomersInfo>Data',
                ],
            ],
            'beforeDelete' => function($model, $id){
                \common\helpers\Customer::deleteCustomer($id, false);
                return 'deleted';
            }

        ],
    ],
    'covered_tables' => ['customers','address_book','customers_emails', 'customers_phones', 'customers_info'],
];