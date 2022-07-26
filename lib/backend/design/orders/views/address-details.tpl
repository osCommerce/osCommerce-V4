{* DEPRECATED use address-details-holder *}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-address">
        <h4>{$smarty.const.T_ADD_DET}</h4>
        {$manager->render('Toolbar')}
    </div>
    <div id="order_management_data_address" class="widget-content fields_style">
        <div class="pr-add-det-wrapp after {if $sameAddress}pr-add-det-wrapp2{/if}">
            <div class="process-order-box">
                <div class="process-order-item process-order-item-1">
                    {$manager->render('Customer', ['manager' => $manager, 'order' => $order])}
                </div>
                {if is_array($order->delivery)}
                <div class="process-order-item process-order-item-2">
                    {if !$sameAddress}
                        <div class="cr-ord-cust cr-ord-cust-saddress">
                            <span>{$smarty.const.T_SHIP_ADDRESS}</span>
                            <div class="order-delivery-address">{\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>', false, false, 'SHIPPING_')}
                                {*if !empty($order->delivery['telephone'])}
                                    <br>{$order->delivery['telephone']}
                                {/if*}
                            </div>
                        </div>
                    {else}
                        <div class="cr-ord-cust cr-ord-cust-saddress">
                            <span>{$smarty.const.CATEGORY_ADDRESS}</span>
                            <div class="order-billing-address order-delivery-address">
                                {\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>', false, false, 'BILLING_')}
                                {*if !empty($order->delivery['telephone'])}
                                    <br>{$order->delivery['telephone']}
                                {/if*}
                            </div>
                        </div>
                    {/if}                    
                </div>
                {/if}
                <div class="process-order-item process-order-item-3">
                    <div class="cr-ord-cust cr-ord-cust-smethod">
                        <span>{$smarty.const.T_SHIP_METH}</span>
                        <div>{$order->info['shipping_method']}</div>
                        <div class="shipping-additional-params">
                            {$manager->render('ShippingExtraInfo', ['manager' => $manager, 'order' => $order])}
                        </div>
                        {if $ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')}
                            {$ext::showDeliveryDate($order->info['delivery_date'])}
                        {/if}
                        <div class="tracking_number" {if strpos(strval($order->info['shipping_class']),'collect')!==false}style="display: none"{/if}> </div>
                    </div>
                    {*{$manager->render('NSHelper', ['order_id' => $order->order_id])}
                    {\backend\models\EP\DataSources::orderView($order->order_id)}*}
                    {$manager->render('ExternalOrders', ['order' => $order])}
                </div>
                {if !$sameAddress}
                    <div class="process-order-item process-order-item-4">
                        <div class="cr-ord-cust cr-ord-cust-baddress">
                            <span>{$smarty.const.TEXT_BILLING_ADDRESS}</span>
                            <div class="order-billing-address">{\common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, '', '<br>', false, false, 'BILLING_')}</div>
                        </div>
                    </div>
                {/if}                
                <div class="process-order-item process-order-item-5">
                    <div class="cr-ord-cust cr-ord-cust-bmethod">
                        <span><a href="{Yii::$app->urlManager->createUrl(['orders/payment-list', 'oID' => $order->order_id])}" class="popup" data-class="popupEditCat">{$smarty.const.T_BILL_METH}</a></span>
                        <div>{$order->info['payment_method']}</div>
                        {if !empty($order->info['purchase_order'])}<div>#{$order->info['purchase_order']}</div>{/if}
                        {$manager->render('PaymentExtraInfo', ['manager' => $manager, 'order' => $order])}
                    </div>
                    {$manager->render('NSHelper', ['order_id' => $order->order_id])}
                    {\backend\models\EP\DataSources::orderView($order->order_id)}
                </div>
            </div>
            {if SHOW_MAP_ORDER_PROCESS == 'True'}
            {if !$sameAddress}
                <div class="pr-add-det-box pr-add-det-box02 after">
                    <div class="pra-sub-box after">
                        <div class="pra-sub-box-map">
                            {$manager->render('Map', ['marker' => 'gmap_markers1'])}
                        </div>
                    </div>
                    <div class="pra-sub-box after">
                        <div class="pra-sub-box-map">
                            {$manager->render('Map', ['marker' => 'gmap_markers2'])}
                        </div>
                    </div>
                    {$manager->render('MapJS', ['addresses' => [ ['address' => $order->delivery , 'marker' => 'gmap_markers1'], ['address' => $order->billing , 'marker' => 'gmap_markers2'] ], 'order' => $order ])}
                </div>
            {else}
                <div class="pr-add-det-box pr-add-det-box02 pr-add-det-box03 after">
                    <div class="pra-sub-box after">
                        <div class="pra-sub-box-map">
                            
                            
                        </div>
                        <div class="pra-sub-box-map">
                            {$manager->render('Map', ['marker' => 'gmap_markers'])}
                        </div>
                        {$manager->render('MapJS', ['addresses' => [ ['address' => $order->delivery , 'marker' => 'gmap_markers'] ], 'order' => $order ])}
                    </div>
                </div>
            {/if}
            {/if}
        </div>
    </div>
</div>