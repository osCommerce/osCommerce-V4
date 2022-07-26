<span class="tracknum">
    {if (is_array($trackings) && count($trackings))}
        {foreach $trackings as $track}
            {assign var="tracking_data" value=\common\helpers\Order::parse_tracking_number($track['tracking_number'])}
            <div class="tracking-first">
                <a href="{$app->urlManager->createUrl(['orders/tracking-edit', 'orders_id' => $orders_id, 'tracking_numbers_id' => $track['tracking_numbers_id']])}" class="edit-tracking">
                    <i class="icon-pencil"></i>
                </a>
                {$tracking_data['carrier']} <a href="{$tracking_data['url']}" target="_blank">{$tracking_data['number']}</a> ({intval($track['products_quantity'])})
                <a href="{$tracking_data['url']}" target="_blank" class="tracking-qr-code"><img alt="{$tracking_data['number']}" src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$orders_id}&cID={$customers_id}&tracking=1&tracking_number={$track['tracking_number']}"></a>
            </div>
            {break}
        {/foreach}

{if $trackings|count > 1}
        <div class="">
            <a href="#contentColumn" class="popup-tracking-number-link">{$smarty.const.MORE_TRACKING_NUMBER}</a>
        </div>
{/if}


            <a href="{$app->urlManager->createUrl(['orders/tracking-edit', 'orders_id' => $orders_id])}"
               class="edit-tracking add-more-tracking"{if false and $products_left == 0} style="display: none" {/if}>
                {$smarty.const.TEXT_ADD_MORE} ({intval($products_left)})
            </a>

    {else}
        <a href="{$app->urlManager->createUrl(['orders/tracking-edit', 'orders_id' => $orders_id])}" class="edit-tracking">
            <i class="icon-pencil"></i>
            {$smarty.const.TEXT_TRACKING_NUMBER}
        </a>
    {/if}
</span>


{if count($trackings) > 1}
    <div id="contentColumn" style="display: none">
        <div class="tracking-numbers-content">
            <div class="popup-heading">{$smarty.const.TRACKING_NUMBERS}</div>

            {foreach $trackings as $track}
                <div class="item">
                {$tracking_data=\common\helpers\Order::parse_tracking_number($track['tracking_number'])}
                <div class="tracking-code">

                    <div class="tracking-number-show">
                        {$tracking_data['carrier']} <a href="{$tracking_data['url']}" target="_blank">{$tracking_data['number']}</a>
                    </div>

                    <a href="{$tracking_data['url']}" target="_blank" class="order-qrcode-img">
                        <img alt="{$tracking_data['number']}" src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$orders_id}&cID={$customers_id}&tracking=1&tracking_number={$track['tracking_number']}">
                    </a>

                    <div style="margin-top: 20px">
                        <a href="{$app->urlManager->createUrl(['orders/tracking-edit', 'orders_id' => $orders_id, 'tracking_numbers_id' => $track['tracking_numbers_id']])}" class="edit-tracking">
                            <i class="icon-pencil"></i>
                        </a>
                    </div>
                    <div style="margin-top: 20px">
                        <span class="remove-tracking" data-order-id="{$orders_id}" data-tracking-id="{$track['tracking_numbers_id']}">
                            <i class="icon-trash"></i>
                        </span>
                    </div>
                </div>

                {if is_array($products_per_tracking[$track['tracking_numbers_id']]) && count($products_per_tracking[$track['tracking_numbers_id']]) > 0}
                    <div class="tracking-products">
                    <table border="0" class="table table-bordered" style="width:95%" align="center" cellspacing="0" cellpadding="2">
                        <tr>
                            <th>{$smarty.const.TABLE_HEADING_QUANTITY}</th>
                            <th>{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                            <th>{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>
                        </tr>
                        {foreach $products_per_tracking[$track['tracking_numbers_id']] as $product}
                            <tr>
                                <td>{if $product['selected_qty'] > 0}{$product['selected_qty']}{else}{$product['qty']}{/if}&nbsp;x</td>
                                <td>
                                    {htmlspecialchars($product['name'])}
                                    {if (isset($product['attributes']) && (sizeof($product['attributes']) > 0))}
                                        {for $j = 0 to (sizeof($product['attributes']) - 1)}
                                            <br><nobr><small>&nbsp;&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($product['attributes'][$j]['option']))}: {htmlspecialchars($product['attributes'][$j]['value'])}
                                                </i></small></nobr>
                                        {/for}
                                    {/if}
                                </td>
                                <td>{$product['model']}</td>
                            </tr>
                        {/foreach}
                    </table>
                    </div>
                {/if}
                </div>
            {/foreach}

        </div>

        <script>
            $(function(){

                $('.remove-tracking').on('click', function(){
                    var _this = $(this);
                    $.popUpConfirm('{$smarty.const.CONFIRM_DELETE_TRACKING_NUMBER}', function(){
                        $.post('{$app->urlManager->createUrl('orders/tracking-delete')}', {
                            'orders_id': _this.data('order-id'),
                            'tracking_numbers_id': _this.data('tracking-id')
                        });
                        _this.closest('.item').remove();
                        $('.add-more-tracking').show()
                    })
                });

                $('a.edit-tracking').popUp({
                    box: "<div class='popup-box-wrap trackWrap'><div class='around-pop-up'></div><div class='popup-box popupCredithistory'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_EDIT_TRACKING_NUMBER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
            })
        </script>
    </div>
{/if}


<script>
    $(function(){

        $('.popup-tracking-number-link').popUp({
            box_class: 'popup-tracking-number'
        });
    });
    $('a.edit-tracking').popUp({
      box: "<div class='popup-box-wrap trackWrap'><div class='around-pop-up'></div><div class='popup-box popupCredithistory'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_EDIT_TRACKING_NUMBER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
</script>