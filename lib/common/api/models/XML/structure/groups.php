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
    'Header' => 'site/groups',
    'Data' => [
        'common\\models\\Groups' => [
            'xmlCollection' => 'Groups>Group',
            'withRelated' => [
                'additionalDiscounts' => [
                    'xmlCollection' => 'AdditionalDiscounts>AdditionalDiscount',
                    'properties' => [
                        'groups_discounts_id' => false,
                    ],
                ],
            ],
            'properties' =>[
                'image_active' => ['class'=>'IOAttachment', 'location'=> '@images/icons'],
                'image_inactive' => ['class'=>'IOAttachment', 'location'=> '@images/icons'],
            ],
        ],
    ],
    'covered_tables' => ['groups',],
];