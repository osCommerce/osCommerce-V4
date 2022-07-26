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
    'Header' => 'site/themes',
    'dependsOn' => [],
    'Data' => [
        'common\\models\\Themes' => [
            'xmlCollection' => 'Themes>Theme',
            'properties' => [
                'themeBackup' => ['class' => 'IOAttachment', /*, 'limitMode' => ['inline']*/]
            ],
            'withRelated' => [
                'assignedToPlatforms' => [
                    'xmlCollection' => 'AssignedToPlatforms>AssignedToPlatform',
                    'properties' =>[

                    ]
                ],
            ],
        ]
    ],
    'covered_tables' => [ 'themes', 'platforms_to_themes', ],
];
