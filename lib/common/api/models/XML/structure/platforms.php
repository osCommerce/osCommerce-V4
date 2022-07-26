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
    'Header' => 'site/platforms',
    'Data' => [
        'common\\models\\Platforms' => [
            'xmlCollection' => 'Platforms>Platform',
            'withRelated' => [
                'platformsAddressBook' => [
                    'xmlCollection' => 'AddressBooks>AddressBook',
                    'properties' => [
                        //'entry_country_id' => 'CountryId',
                        'entry_country_id' => ['rename'=>'CountryId', 'class' => 'IOCountryMap'],
                    ],
                ],
                'platformsOpenHours' => [
                    'xmlCollection' => 'OpenHours>OpenHour',
                    'properties' => [
                        'platforms_open_hours_id' => false,
                    ],
                ],
                'platformsCutOffTimes' => [
                    'xmlCollection' => 'CutOffTimes>CutOffTime',
                    'properties' => [
                        'platforms_cut_off_times_id' => false,
                    ],
                ],
                'platformsHolidays' => [
                    'xmlCollection' => 'PlatformsHolidays>PlatformsHoliday',
                    'properties' => [
                        'platforms_holidays_id' => false,
                    ],
                ],
                'platformsWatermark' => [
                    'xmlCollection' => 'PlatformsWatermarks>PlatformsWatermark',
                    'properties' => [
                        'watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'left_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'left_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'left_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'right_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'right_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'right_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_left_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_left_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_left_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_right_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_right_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'top_right_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_left_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_left_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_left_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_right_watermark30' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_right_watermark170' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                        'bottom_right_watermark300' => ['class'=>'IOAttachment', 'path'=>'@images/stamp'],
                    ],
                ],
            ],
        ],
    ],
    'covered_tables' => ['platforms','platforms_address_book', 'platforms_open_hours', 'platforms_cut_off_times', 'platforms_holidays', 'platforms_watermark', ],
];