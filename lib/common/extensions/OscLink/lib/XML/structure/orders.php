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

return array(
    'Header' => [
        'type' => 'site/orders',
    ],
    'dependsOn' => ['site/languages', 'site/order_statuses', 'site/currencies', 'site/platforms', 'site/customers', 'site/products', 'site/countries'],
    'Data' => array(
        'common\\models\\Orders' => array(
            //'where' => ['orders_id'=>110610],
            'xmlCollection' => 'Orders>Order',
            'properties' => [
                'customers_id' => ['class'=>'IOMap', 'table'=>'customers','attribute'=>'customers_id'],
                'orders_status' => ['class'=>'IOOrderStatus'],
                'language_id' => ['class'=>'IOLanguageMap'],
            ],
            'beforeImportSave' => function($model, $data){
                if (!\OscLink\XML\IOCore::get()->isLocalProject()) {
                    if (isset($data->data['orders_id']->externalId)) {
                        $model->external_orders_id = $data->data['orders_id']->externalId;
                    } elseif (isset($data->data['orders_id']->internalId)) {
                        $model->external_orders_id = $data->data['orders_id']->internalId;
                    }
                }
            },
            'withRelated' => array(
                'ordersProducts' => array(
                    'xmlCollection' => 'OrdersProducts>OrdersProduct',
                    'properties' => [
                        'orders_products_id'=>false,
                    ],
                    'withRelated' => array(
                        'ordersProductsAttributes' => array(
                            'xmlCollection' => 'Attributes>Attribute',
                            'properties' => [
                                'orders_products_attributes_id'=>false,
                            ],
                        )
                    ),
                ),
                'ordersTotals' => array(
                    'xmlCollection' => 'OrdersTotals>OrdersTotal',
                    'properties' => [
                        'orders_total_id'=>false,
                    ],
                ),
                'ordersStatusHistory' => array(
                    'xmlCollection' => 'OrdersStatusHistory>OrdersStatus',
                    'properties' => [
                        'orders_status_history_id' => false, //???
                        'orders_status_id' => ['class'=>'IOOrderStatus'],
                    ]
                )
            ),
            'beforeDelete' => function($model, $id) {
                \common\helpers\Order::remove_order($id, '', 'OscLink cleaned');
                return 'deleted';
            },
            'afterImport' => function($model, $data) {
                if (isset($data->data['orders_id']) && is_object($data->data['orders_id'])) {
                    $tools = new \backend\models\EP\Tools();
                    $orders_id = $data->data['orders_id']->toImportModel();
                    $platform_id = \common\classes\platform::defaultId();
                    \common\models\Orders::updateAll(['platform_id' => $platform_id], 'platform_id = 0');
                    $language_id = \common\helpers\Language::get_default_language_id();
                    \common\models\Orders::updateAll(['language_id' => $language_id], 'language_id = 0');
                    foreach (\common\models\OrdersProducts::findAll(['orders_id' => $orders_id, 'uprid' => '']) as $opRecord) {
                        // fill uprid if empty
                        $attributes_array = array();
                        foreach (\common\models\OrdersProductsAttributes::findAll(['orders_id' => $orders_id, 'orders_products_id' => $opRecord->orders_products_id]) as $opaRecord) {
                            if ($opaRecord->products_options_id == 0) {
                                $opaRecord->products_options_id = $tools->get_option_by_name($opaRecord->products_options);
                            }
                            if ($opaRecord->products_options_values_id == 0) {
                                $opaRecord->products_options_values_id = $tools->get_option_value_by_name($opaRecord->products_options_id, $opaRecord->products_options_values);
                            }
                            if ($opaRecord->products_options_id && $opaRecord->products_options_values_id) {
                                $attributes_array[$opaRecord->products_options_id] = $opaRecord->products_options_values_id;
                            }
                            $opaRecord->save(false);
                        }
                        $opRecord->uprid = \common\helpers\Inventory::get_uprid($opRecord->products_id, $attributes_array);
                        $opRecord->save(false);
                    }
                }
            },
        ),
    ),
    'covered_tables' => array('orders', 'orders_products', 'orders_products_attributes', 'orders_total', 'orders_status_history'),
);