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
    'Header' => 'site/order_status_group',
    'dependsOn' => ['site/languages',],
    'Data' => [
        'common\\models\\OrdersStatusGroups' => [
            'xmlCollection' => 'OrdersStatusGroups>OrdersStatusGroup',
            'properties' => [
                'language_id' => ['class'=>'IOLanguageMap']
            ],
        ],
    ],
    'covered_tables' => ['orders_status_groups',],
];