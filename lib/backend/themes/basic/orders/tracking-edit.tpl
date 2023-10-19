{use class="\common\helpers\Html"}
{use class="\common\helpers\Date"}
<div id="trackingNumber">
    <form name="savetrack" method="post" onSubmit="return saveTracking();">
        <input type="hidden" name="orders_id" value="{$orders_id}">
        <input type="hidden" name="tracking_numbers_id" value="{$tracking_numbers_id}">
    <div class="trackingBox">
    {if $tracking_numbers_id > 0}
        {assign var="tracking_data" value=\common\helpers\Order::parse_tracking_number($tracking_number)}
        <div class="row">
            <div><input name="tracking_number" type="hidden" value="{$tracking_number}" class="form-control"></div>
            <div class="t_number">{$tracking_data['number']}</div>
            <div class="edit-pt"><i class="icon-pencil"></i></div>
        </div>
        <div class="row">
            <a href="{$tracking_data['url']}" target="_blank"><img src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$orders_id}&cID={$customers_id}&tracking=1&tracking_number={$tracking_number}"></a>
        </div>
        <div class="row">
            <a href="javascript:void(0)" class="btn btn-delete" onclick="if (confirm('{$smarty.const.CONFIRM_DELETE_TRACKING_NUMBER}')) { return deleteTracking(); }">{$smarty.const.IMAGE_DELETE}</a>
        </div>
    {else}
        <div class="row">
            <input name="tracking_number" type="text" value="" class="form-control">
        </div>
    {/if}
    </div>
    {if $sync && !empty($sync['transactions'])}
        <input type="hidden" name='platform_id' value='{$platform_id}' />
        <div class="sync" style="padding:0px 20px 10px">
            {if !$sync['added']}
                <div class="widget-content">
                    <span class="intro">{$smarty.const.TEXT_TRACKING_ADD_TO_PAYMENT_GATEWAY}</span><br/>
                {foreach $sync['transactions'] as $s}
                    <span>{Html::checkbox('sync_to_payment['|cat:$s['id']|cat:']', false, ['value' => $s['id']])}
                        <label for="syncToPayment_{$s['id']}_">{$s['payment']} {sprintf($smarty.const.TEXT_TRACKING_TRANSACTION, $s['transaction'], Date::date_short($s['paid_on']))}</label>
                    </span>
                {/foreach}
                </div>

            {else}
                {foreach $sync['transactions'] as $s}
                <span title="{Date::datetime_short($s['date_added'])}">{sprintf($smarty.const.TEXT_TRACKING_ADD_DETAILS, sprintf($smarty.const.TEXT_TRACKING_TRANSACTION, $s['payment'], $s['transaction']), Date::date_short($s['date_added']))}</span>
                {/foreach}
            {/if}
        </div>
    {/if}
    {if is_array($orders_products) && count($orders_products) > 0}
        <table border="0" class="table table-bordered" style="width:95%" align="center" cellspacing="0" cellpadding="2">
            <thead>
                <tr>
                    <th class="prod-edit"><input type="checkbox" id="checkAll" /></th>
                    <th class="prod-edit"><i class="icon-pencil" style="color: #0077e4;" onclick="switchEditView();"></i> {$smarty.const.TABLE_HEADING_QUANTITY}</th>
                    <th class="prod-veiw"><i class="icon-pencil" style="color: #0077e4;" onclick="switchEditView();"></i> {$smarty.const.TABLE_HEADING_QUANTITY}</th>
                    <th colspan="2">{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
                    <th>{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $orders_products as $orders_products_id => $product}
                <tr>
                    <td class="prod-edit"><input type="checkbox" name="selected_products[{$orders_products_id}]" value="{$orders_products_id}"{if $product['selected']} checked disabled{/if}{if $product['qty_max'] <= 0} disabled{/if} /></td>
                    <td class="plus_td box_al_center prod-edit" align="center"><span class="pr_minus"></span><input type="text" name="selected_products_qty[{$orders_products_id}]" size="2" value="{$product['qty']}" data-min="{$product['qty_min']}" data-max="{$product['qty_max']}" opid="{$orders_products_id}" class="form-select qty" /><span class="pr_plus"></span><input type="hidden" name="selected_products_qty_max[{$orders_products_id}]" value="{$product['qty_max']}" /></td>
                    <td class="prod-veiw"><span opid="{$orders_products_id}">{$product['qty']}</span>&nbsp;x</td>
                    <td class="left"><div class="table-image-cell"><a href="{\common\classes\Images::getImageUrl($product['id'], 'Large')}" class="fancybox">{\common\classes\Images::getImage($product['id'])}</a></a></div></td>
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
            </tbody>
        </table>
    {/if}
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left">
            <a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return closePopup();">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
    </form>
</div>
<script>
    function saveTracking() {
        $(window).scrollTop(0);
        $("input[name^='selected_products_qty[']").each(function() {
            $(this).change();
            if ($(this).attr('data-max') == 0) {
                $('input[name="selected_products[' + $(this).attr('opid') + ']"]:checkbox').prop('checked', false);
            }
        });
        $.post("{$app->urlManager->createUrl('orders/tracking-save')}", $('form[name=savetrack]').serialize(), function(data, status) {
            if (status == "success") {
              getTrackingList();
              setTimeout(function() {
                closePopup();
              },500);
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    function deleteTracking() {
        $(window).scrollTop(0);
        $.post("{$app->urlManager->createUrl('orders/tracking-delete')}", $('form[name=savetrack]').serialize(), function(data, status) {
            if (status == "success") {
              getTrackingList();
              setTimeout(function() {
                closePopup();
              },500);
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    function switchEditView() {
        if ($('.prod-edit').is(":visible")) {
            $('.prod-edit').hide();
            $('.prod-veiw').show();
            $("input[name^='selected_products[']:checkbox").each(function() {
                if ($(this).prop('checked')) {
                    $(this).closest('tr').show();
                } else {
                    $(this).closest('tr').hide();
                }
            })
        } else {
            $('.prod-edit').show();
            $('.prod-veiw').hide();
            $("input[name^='selected_products[']:checkbox").each(function() {
                $(this).closest('tr').show();
            })
        }
    }
    $('#trackingNumber .edit-pt').click(function() {
        var input = $(this).prev().prev().find('input');
        var div = $(this).prev();
        var img = $(this).next().find('img');

        if ($(this).hasClass('btn')) {
            $(this).removeClass('btn');
            $(div).html($(input).val()).show();
            $(input).attr('type', 'hidden').hide();
            if($(input).val().length > 0) {
                $(img).attr('src', '{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$orders_id}&cID={$customers_id}&tracking=1&tracking_number='+$(input).val());
            } else {
                $(img).attr('src', '');
            }
        } else {
            $(this).addClass('btn');
            $(div).hide();
            $(input).attr('type', 'text').show();
        }
    })
    $('#checkAll').change(function () {
        $("input[name^='selected_products[']:checkbox:enabled").prop('checked', $(this).prop('checked'));
    });
    $('.pr_plus').click(function() {
        var input = $(this).prev('input');
        var val = input.val();
        var max = parseInt(input.attr('data-max'));
        if (val < max) {
            val ++;
        }
        input.val(val);
        $('span[opid="' + input.attr('opid') + '"]').html(val);
    });
    $('.pr_minus').click(function() {
        var input = $(this).next('input');
        var val = input.val();
        var min = parseInt(input.attr('data-min'));
        if (val > min) {
            val --;
        }
        input.val(val);
        $('span[opid="' + input.attr('opid') + '"]').html(val);
    });
    $("input[name^='selected_products_qty[']").unbind('change').bind('change', function() {
        if ($(this).val() > $(this).attr('data-max')) {
            $(this).val($(this).attr('data-max'));
        }
        if ($(this).val() < $(this).attr('data-min')) {
            $(this).val($(this).attr('data-min'));
        }
    }).unbind('keypress').bind('keypress', function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            event.preventDefault();
            return $(this).change();
        }
        return true;
    });
{if $tracking_numbers_id > 0}
    switchEditView();
{else}
    switchEditView();
    switchEditView();
{/if}
</script>