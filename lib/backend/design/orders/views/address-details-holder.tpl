
<div class="pr-add-det-wrapp after {if $sameAddress}pr-add-det-wrapp2{else}pr-add-det-wrapp1{/if}">
    <div class="process-order-box">
        <div class="process-order-item process-order-item-1">
            {$manager->render('Customer', ['manager' => $manager, 'order' => $order])}
        </div>
        {if is_array($order->delivery)}
        <div class="process-order-item process-order-item-2">
            {if !$sameAddress}
                <div class="cr-ord-cust cr-ord-cust-saddress">
                    <div class="heading"><i class="osci-address"></i> {$smarty.const.T_SHIP_ADDRESS}</div>
                    <div class="order-delivery-address">{\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>', false, false, 'SHIPPING_')}
                    </div>
                </div>
            {else}
                <div class="cr-ord-cust cr-ord-cust-saddress">
                    <div class="heading"><i class="osci-address"></i> {$smarty.const.CATEGORY_ADDRESS}</div>
                    <div class="order-billing-address order-delivery-address">
                        {\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>', false, false, 'BILLING_')}
                    </div>
                </div>
            {/if}
        </div>
        {/if}
        <div class="process-order-item process-order-item-3">
            <div class="cr-ord-cust cr-ord-cust-smethod">
                <div class="heading"><i class="osci-shipping"></i> {$smarty.const.T_SHIP_METH}</div>
                <div>{$order->info['shipping_method']}</div>
                <div class="shipping-additional-params">
                    {$manager->render('ShippingExtraInfo', ['manager' => $manager, 'order' => $order])}
                </div>
                {if $ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')}
                    {$ext::showDeliveryDate($order->info['delivery_date'])}
                {/if}
                <div class="tracking_number" {if strpos(strval($order->info['shipping_class']),'collect')!==false}style="display: none"{/if}> </div>
            </div>
            {$manager->render('ExternalOrders', ['order' => $order])}
        </div>
        {if !$sameAddress}
            <div class="process-order-item process-order-item-4">
                <div class="cr-ord-cust cr-ord-cust-baddress">
                    <div class="heading"><i class="osci-billing-method"></i> {$smarty.const.TEXT_BILLING_ADDRESS}</div>
                    <div class="order-billing-address">{\common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, '', '<br>', false, false, 'BILLING_')}</div>
                </div>
            </div>
        {/if}
        <div class="process-order-item process-order-item-5">
            <div class="cr-ord-cust cr-ord-cust-bmethod">
                <div class="heading"><i class="osci-billing-method"></i> {$smarty.const.T_BILL_METH}<a href="{Yii::$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $order->order_id])}" class="popup" data-class="popupEditCat"> </a></div>
                <div>{$order->info['payment_method']}</div>
                {if !empty($order->info['purchase_order'])}<div><span class="num-sharp">#</span>{$order->info['purchase_order']}</div>{/if}
                {$manager->render('PaymentExtraInfo', ['manager' => $manager, 'order' => $order])}
            </div>
            {$manager->render('NSHelper', ['order_id' => $order->order_id])}
            {\backend\models\EP\DataSources::orderView($order->order_id)}
        </div>
    </div>
</div>

