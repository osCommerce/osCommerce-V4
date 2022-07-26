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

namespace backend\models\EP\Provider\Trueloaded;


class TrueloadedXmlFeedProvider
{

    public static function getProviderList()
    {

        $feeds = [
            'Trueloaded\\Platforms' => [
                'group' => 'Trueloaded',
                'name' => 'Platforms',
                'class' => 'Provider\\Trueloaded\\Platforms',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Customers' => [
                'group' => 'Trueloaded',
                'name' => 'Customers',
                'class' => 'Provider\\Trueloaded\\Customers',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform'],
                ],
            ],
            'Trueloaded\\Orders' => [
                'group' => 'Trueloaded',
                'name' => 'Orders',
                'class' => 'Provider\\Trueloaded\\Orders',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform', 'orders-date-range'],
                ],
            ],
            'Trueloaded\\Quotes' => [
                'group' => 'Trueloaded',
                'name' => 'Quotations',
                'class' => 'Provider\\Trueloaded\\Quotes',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform', 'orders-date-range'],
                ],
            ],
            'Trueloaded\\OrdersStatusGroups' => [
                'group' => 'Trueloaded',
                'name' => 'Order Statuses Groups',
                'class' => 'Provider\\Trueloaded\\OrdersStatusGroups',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\OrdersStatuses' => [
                'group' => 'Trueloaded',
                'name' => 'Order Statuses',
                'class' => 'Provider\\Trueloaded\\OrdersStatuses',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Brands' => [
                'group' => 'Trueloaded',
                'name' => 'Brands',
                'class' => 'Provider\\Trueloaded\\Brands',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Countries' => [
                'group' => 'Trueloaded',
                'name' => 'Countries',
                'class' => 'Provider\\Trueloaded\\Countries',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Tax' => [
                'group' => 'Trueloaded',
                'name' => 'Tax',
                'class' => 'Provider\\Trueloaded\\Tax',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\TaxZones' => [
                'group' => 'Trueloaded',
                'name' => 'Tax Zones',
                'class' => 'Provider\\Trueloaded\\TaxZones',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Currencies' => [
                'group' => 'Trueloaded',
                'name' => 'Currencies',
                'class' => 'Provider\\Trueloaded\\Currencies',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Groups' => [
                'group' => 'Trueloaded',
                'name' => 'Groups',
                'class' => 'Provider\\Trueloaded\\Groups',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Languages' => [
                'group' => 'Trueloaded',
                'name' => 'Languages',
                'class' => 'Provider\\Trueloaded\\Languages',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Categories' => [
                'group' => 'Trueloaded',
                'name' => 'Categories',
                'class' => 'Provider\\Trueloaded\\Categories',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Products' => [
                'group' => 'Trueloaded',
                'name' => 'Products',
                'class' => 'Provider\\Trueloaded\\Products',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Warehouses' => [
                'group' => 'Trueloaded',
                'name' => 'Warehouses',
                'class' => 'Provider\\Trueloaded\\Warehouses',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\Suppliers' => [
                'group' => 'Trueloaded',
                'name' => 'Suppliers',
                'class' => 'Provider\\Trueloaded\\Suppliers',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\ProductsStockIndication' => [
                'group' => 'Trueloaded',
                'name' => 'Products Stock Indication',
                'class' => 'Provider\\Trueloaded\\ProductsStockIndication',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\ProductsStockDeliveryTerms' => [
                'group' => 'Trueloaded',
                'name' => 'Products Stock Delivery Terms',
                'class' => 'Provider\\Trueloaded\\ProductsStockDeliveryTerms',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                ],
            ],
            'Trueloaded\\ProductsOptions' => [
                'group' => 'Trueloaded',
                'name' => 'Products Options',
                'class' => 'Provider\\Trueloaded\\ProductsOptions',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
            'Trueloaded\\Themes' => [
                'group' => 'Trueloaded',
                'name' => 'Themes',
                'class' => 'Provider\\Trueloaded\\Themes',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                ],
            ],
        ];
        foreach ($feeds as $key=>$val){
            if ( isset($feeds[$key]['export']) && is_array($feeds[$key]['export']) ) {
                if ( is_array($feeds[$key]['export']['filters'] ?? null) ) {
                    $feeds[$key]['export']['filters'][] = 'project';
                }
                if ( !isset($feeds[$key]['export']['disableSelectFields']) ) {
                    $feeds[$key]['export']['disableSelectFields'] = true;
                }
            }
            if ( isset($feeds[$key]['import']) && is_array($feeds[$key]['import']) ) {
                $feeds[$key]['import'] = array_merge($feeds[$key]['import'],[
                    'format' => 'XML',
                ]);
            }else{
                $feeds[$key]['import'] = [
                    'format' => 'XML',
                ]; 
            }
        }
        return $feeds;
    }

}