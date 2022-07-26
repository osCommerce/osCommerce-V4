<span class="tracknum">
    
    {if (is_array($trackings) && count($trackings))}
        {foreach $trackings as $tkey => $track}
            {assign var="tracking_data" value=\common\helpers\Order::parse_tracking_number($track)}
            <div class="row" {if $tkey>0}style='display:none'{/if}>
                {if $tkey eq 0}
                <a href="{$app->urlManager->createUrl(['orders/gettracking', 'orders_id' => $order_id])}" class="edit-tracking">
                    <i class="icon-pencil"></i>
                </a>
                {/if}
                <a href="{$tracking_data['url']}" target="_blank">{$tracking_data['number']}</a>
                <a href="{$tracking_data['url']}" target="_blank"><img alt="{$tracking_data['number']}" src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$order_id}&cID={$customers_id}&tracking=1&tracking_number={$track}"></a>
            </div>            
        {/foreach}
        {if $trackings|count>1}
                <a href="{$app->urlManager->createUrl(['orders/gettracking', 'orders_id' => $order_id])}" class="edit-tracking">
                <i class="icon-qrcode"></i>
                {$smarty.const.TEXT_SHOW_TRACKING_NUMBERS|replace:'::count::':($trackings|count-1)}
                </a>
            {/if}
    {else}
        <a href="{$app->urlManager->createUrl(['orders/gettracking', 'orders_id' => $order_id])}" class="edit-tracking">
            <i class="icon-pencil"></i>
            {$smarty.const.TEXT_TRACKING_NUMBER}
        </a>
    {/if}

</span>
<script>
$('a.edit-tracking').popUp({
      box: "<div class='popup-box-wrap trackWrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_EDIT_TRACKING_NUMBER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
</script>