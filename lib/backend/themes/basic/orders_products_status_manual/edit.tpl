{tep_draw_form('status', 'orders_products_status_manual/save', 'orders_products_status_manual_id='|cat:$orders_products_status_manual_id)}
    {if $languages|count > 1}
    <div class="tabbable-custom" style="margin-top: 20px">
        <ul class="nav nav-tabs ">
            {foreach $languages as $lKey => $lItem}
                <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
            {/foreach}
        </ul>
        <div class="tab-content">
    {/if}
        {foreach $languages as $lKey => $lItem}
            {if $languages|count > 1}<div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$lItem['code']}">{/if}
            <div class="row">
                <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_PRODUCTS_STATUS_NAME_LONG}</label></div>
                <div class="col-md-4">
                    {$orders_products_status_manual_inputs_string_long[$lItem.id]}
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_PRODUCTS_STATUS_NAME}</label></div>
                <div class="col-md-4">
                    {$orders_products_status_manual_inputs_string[$lItem.id]}
                </div>
            </div>
            {if $languages|count > 1}</div>{/if}
        {/foreach}
    {if $languages|count > 1}
        </div>
    </div>
    {/if}
    <div class="row">
        <div class="col-md-2"><label>{$smarty.const.TEXT_INFO_ORDERS_PRODUCTS_STATUS_COLOUR}</label></div>
        <div class="col-md-4">
            <div class="colors-inp">
                <div id="cp3" class="input-group colorpicker-component">
                    <input type="text" name="orders_products_status_manual_colour" value="{$orders_products_status_manual_colour}" class="form-control" placeholder="{$smarty.const.TEXT_INFO_ORDERS_PRODUCTS_STATUS_COLOUR}" />
                    <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
                </div>
            </div>
        </div>
    </div>
    {foreach $orders_products_status_matrix_string as $opsArray}
        <div class="row">
            <div class="col-md-2">{$opsArray['label']}</div>
            <div class="col-md-4">{$opsArray['element']}</div>
        </div>
    {/foreach}
    <div class="btn-bar" style="padding: 0;">
        <div class="btn-left">
            <a href="{Yii::$app->urlManager->createUrl(['orders_products_status_manual', 'orders_products_status_manual_id' => $orders_products_status_manual_id])}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_UPDATE}</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(function() {
        $('.btn-confirm').on('click', (function(e) {
            e.preventDefault();
            var form = $('form[name="status"]');
            var data = form.serializeArray();
            var action = form.attr('action');
            $.post(action, data, function(response) {
                alertMessage('<div class="popup-content pop-mess-cont">' + response.message + '</div>');
                if (response.added) {
                    window.location.href = "orders_products_status_manual/edit?orders_products_status_manual_id=" + response.added;
                }
                setTimeout(function() {
                    $('.popup-box-wrap:last').remove();
                }, 1000);
            }, 'json');
            return false;
        }));

        var createColorpicker = (function() {
            setTimeout(function() {
                var cp = $('.colorpicker-component:not(.colorpicker-element)');
                cp.colorpicker({
                    sliders: {
                        saturation: { maxLeft: 200, maxTop: 200 },
                        hue: { maxTop: 200 },
                        alpha: { maxTop: 200 }
                    }
                });
                var removeColorpicker = (function() {
                    cp.colorpicker('destroy');
                    cp.closest('.popup-box-wrap').off('remove', removeColorpicker)
                    $('.style-tabs-content').off('st_remove', removeColorpicker)
                });
                cp.closest('.popup-box-wrap').on('remove', removeColorpicker);
                $('.style-tabs-content').on('st_remove', removeColorpicker);
            }, 200)
        });
        createColorpicker();
    });
</script>