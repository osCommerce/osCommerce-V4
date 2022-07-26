{if $products_left > 0}
    <a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $order->order_id])}" TARGET="_blank" class="btn btn-primary popup">{$smarty.const.TEXT_PRINT_LABEL}{if count($shipments_array) > 0} - {$smarty.const.TEXT_ADD_MORE}{/if}</a>
{/if}
{foreach $shipments_array as $label}
    {if is_object($label['class'])}
        <br>{$label['class']->title} {$label['tracking_number']}
        {if $label['label_module_error']}<span class="text-danger">{$label['label_module_error']}</span>{/if}
        <a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $order->order_id, 'orders_label_id' => $label['orders_label_id']])}" TARGET="_blank" class="btn btn-primary popup">{$smarty.const.TEXT_PRINT_LABEL}</a>
        {if $label['class']->shipment_exists($order->order_id, $label['orders_label_id'])}
            {if $label['class']->can_update_shipment}<a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $order->order_id, 'orders_label_id' => $label['orders_label_id'], 'action' => 'update'])}" class="btn btn-primary popup">{$smarty.const.TEXT_UPDATE_LABEL}</a>{/if}
            {if $label['class']->can_cancel_shipment}<a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $order->order_id, 'orders_label_id' => $label['orders_label_id'], 'action' => 'cancel'])}" class="btn btn-mar-right btn-primary popup">{$smarty.const.TEXT_CANCEL_LABEL}</a>{/if}
        {else}
            <a href="{\Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $order->order_id, 'orders_label_id' => $label['orders_label_id'], 'action' => 'delete'])}" class="btn btn-mar-right btn-primary popup">{$smarty.const.TEXT_CANCEL_LABEL}</a>
        {/if}
    {/if}
{/foreach}
