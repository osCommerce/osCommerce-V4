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

namespace backend\models\EP\Datasource;

use backend\models\EP\DatasourceBase;
use backend\models\EP\DataSources;
use backend\models\EP\Directory;

class OSCommerce extends DatasourceBase {

    protected $remoteData = [];

    public function getName() {
        return 'OSCommerce 2.2-2.3';
    }

    public static function getProviderList() {
        return [
            'OSCommerce\\ImportBrands' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Brands',
                'class' => 'Provider\\Trueloaded\\ImportBrands',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportCategories' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Categories',
                'class' => 'Provider\\Trueloaded\\ImportCategories',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportProductsOptions' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Product Options',
                'class' => 'Provider\\Trueloaded\\ImportProductsOptions',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportProducts' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Products',
                'class' => 'Provider\\Trueloaded\\ImportProducts',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportCustomers' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Customers',
                'class' => 'Provider\\Trueloaded\\ImportCustomers',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportOrdersStatuses' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Orders Statuses',
                'class' => 'Provider\\Trueloaded\\ImportOrdersStatuses',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportOrders' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Orders',
                'class' => 'Provider\\Trueloaded\\ImportOrders',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportTaxZones' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Tax Zones',
                'class' => 'Provider\\Trueloaded\\ImportTaxZones',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
            'OSCommerce\\ImportTax' => [
                'group' => 'OSCommerce 2.2-2.3',
                'name' => 'Import Taxes',
                'class' => 'Provider\\Trueloaded\\ImportTax',
                'export' => [
                    'disableSelectFields' => true,
                ],
            ],
        ];
    }

    public function getViewTemplate() {
        return 'datasource/trueloaded.tpl';
    }

    public function prepareConfigForView($configArray) {
        return parent::prepareConfigForView($configArray);
    }

}
