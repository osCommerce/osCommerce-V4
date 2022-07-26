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
    'Header' => 'site/order_statuses',
    'dependsOn' => ['site/languages', 'site/order_status_group',],
    'Data' => [
        'common\\models\\OrdersStatus' => [
            'xmlCollection' => 'OrdersStatuses>OrdersStatus',
            'properties' => [
                'orders_status_groups_id' => ['class'=>'IOMap', 'table'=>'orders_status_groups', 'attribute'=>'orders_status_groups_id'],
                'language_id' => ['class'=>'IOLanguageMap']
            ],
            'afterImport' => function($model, $data) {
                \common\models\OrdersStatus::updateAll(['orders_status_groups_id' => 1, 'orders_status_allocate_allow' => 1], 'orders_status_groups_id = 0');
            }
        ],
    ],
    'covered_tables' => ['orders_status',],
];