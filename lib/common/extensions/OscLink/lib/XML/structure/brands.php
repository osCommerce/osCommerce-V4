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
    'Header' => 'site/brands',
    'dependsOn' => ['site/languages'],
    'Data' => [
        'common\\api\\models\\AR\\Manufacturer' => [
            'xmlCollection' => 'Brands>Brand',
            'properties' => [
                'manufacturers_image' => ['class'=>'IOAttachment', 'location'=>'@images'],
            ],
            'withRelated' => [
                'infos' => [
                    'xmlCollection' => 'InformationArray>Information',
                    'properties' => [
                        'languages_id' => ['class' => 'IOLanguageMap'],
                    ],
                ],
//                'seoRedirectsNamed' => [
//                    'xmlCollection' => 'SeoRedirects>SeoRedirect',
//                    'properties' => [
//                        'seo_redirects_named_id' => false,
//                        'language_id' => ['class' => 'IOLanguageMap'],
//                        'redirects_type' => false,
//                    ],
//                ],
            ],
        ],
    ],
    'covered_tables' => ['manufacturers','manufacturers_info',],
];

