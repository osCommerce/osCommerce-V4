<div class="order-total-items">
    <div>
        <b>
            <span>{$items}</span> {$smarty.const.ITEMS_IN_TOTAL}            
        </b>
    </div>
    {if $pData && !empty($pData->collection_points_text|default:null)}
    <div>
        <b>
            <span>{$smarty.const.TEXT_ORDER_POINT_TO_TEXT}:</span>
        </b>
        <pre>{$pData->collection_points_text}</pre>
    </div>
    {/if}
    {if $shipping_weight > 0}
    <div>{$smarty.const.TEXT_ACTUAL_WEIGHT_NP}:<b> {number_format($shipping_weight, 2)}</b></div>
    {/if}
    {$volume = \common\helpers\Order::getOrderVolumeWeight($order->order_id)}
    {if $volume > 0}
        <div>{$smarty.const.TEXT_ACTUAL_VOLUME_WEIGHT_KG}:<b> {number_format($volume, 2)}</b></div>
    {/if}
    {foreach \common\helpers\Hooks::getList('orders/process-order', 'totals-block') as $filename}
        {include file=$filename}
    {/foreach}
    <div>
    {if $parent}
        <pre>{$smarty.const.TEXT_CREATED_BY} {$parent->getShortName()}:{if $parent->getShortName()=='TmpOrder'}<a href='{$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $parent->model->orders_id])}' target="_blank">{$parent->model->orders_id}</a>{else}{$parent->model->orders_id}{/if}</pre>
    {/if}
    </div>
</div>
