<div {if $autoSendForm}style="display: none;" {/if}>
<div class="popup-heading">{$smarty.const.TEXT_PRINT_LABEL}</div>
<div class="popup-content">
    <form name="save_label_products" method="get" onSubmit="return saveLabelProducts();">
        <input type="hidden" name="action" value="save_label_products">
        <input type="hidden" name="orders_id" value="{$orders_id}">
        <input type="hidden" name="orders_label_id" value="{$orders_label_id}">
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
                    <td class="prod-edit"><input type="checkbox" name="selected_products[{$orders_products_id}]" value="{$orders_products_id}"{if $product['selected']} checked=""{/if} /></td>
                    <td class="plus_td box_al_center prod-edit" align="center"><span class="pr_minus"></span><input type="text" name="selected_products_qty[{$orders_products_id}]" size="2" value="{if $product['selected_qty'] > 0}{$product['selected_qty']}{else}{$product['qty']}{/if}" data-min="1" data-max="{$product['qty']}" class="form-control qty" /><span class="pr_plus"></span><input type="hidden" name="selected_products_qty_max[{$orders_products_id}]" value="{$product['qty']}" /></td>
                    <td class="prod-veiw">{if $product['selected_qty'] > 0}{$product['selected_qty']}{else}{$product['qty']}{/if}&nbsp;x</td>
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
</div>
<script>
    function saveLabelProducts() {
        //$(window).scrollTop(0);
        $("input[name^='selected_products_qty[']").each(function() {
            $(this).change();
            if ($(this).attr('data-max') == 0) {
                $('input[name="selected_products[' + $(this).attr('opid') + ']"]:checkbox').prop('checked', false);
            }
        });
        $.get("{$app->urlManager->createUrl('orders/print-label')}", $('form[name=save_label_products]').serialize(), function(data, status) {
            if (status == "success") {
                $('.pop-up-content:last').html(data);
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
{if $orders_label_id > 0}
    switchEditView();
{else}
    switchEditView();
    switchEditView();
{/if}
{if $autoSendForm}saveLabelProducts();{/if}
</script>
