<style>
    .labelWidget {
        display: none;
    }
</style>
<div class="popup-heading">{$smarty.const.TEXT_PRINT_LABEL}</div>
<div class="popup-content">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font">
        <tr>
            <td>
    {if {$app->controller->view->errors|default:array()|@count} > 0}
        {foreach $app->controller->view->errors as $error}
            <div class="error">{$error}</div>
        {/foreach}
    {/if}

    {if $hypashipTracking.exist == 1}
        You already have tracking number: <b>{$hypashipTracking.tracking_number}</b>
    {elseif isset($hypashipTracking.exist) && $hypashipTracking.exist == 0}
        Your tracking number: <b>{$hypashipTracking.tracking_number}</b>
    {elseif $app->controller->view->methods|default:array()|@count > 0}
            <form id="select_method_form" name="select_method" onSubmit="return selectMethod();">
                <input type="hidden" name="orders_id" value="{$orders_id}">
                <input type="hidden" name="orders_label_id" value="{$orders_label_id}">
                <input type="hidden" name="all_methods" value="{$all_methods}">
                
<!--=== Accordion ===-->

<div class="panel-group" id="accordion">
        {foreach $app->controller->view->methods as $key => $methods}
            <div class="panel panel-default">
                    <div class="panel-heading">
                            <h3 class="panel-title">
                            <a class="accordion-toggle" data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse{$key}">{$methods.title}</a>
                            </h3>
                    </div>
                    <div id="collapse{$key}" class="panel-collapse collapse{if $methods.accordion} in{/if}">
                            <div class="panel-body">
                                {foreach $methods.methods as $code => $name}
                                    <div class="labelWrap">
                                        <label>
                                            <input type="radio" name="method" value="{$code}"{if $methods.selected==$code} checked="checked"{/if}>
                                            {if is_array($name)}
                                                {$name.name}
                                                <div class="labelWidget">
                                                    {$name.widget}
                                                </div>
                                            {else}
                                                {$name}
                                            {/if}
                                        </label>
                                    </div>
                                {/foreach}
                            </div>
                    </div>
            </div>
            
            
                
        {/foreach}
</div>
							      
                
        

                <div style="padding: 0; text-align: center;" class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return cancelStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
                <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SELECT}</button></div>
        {if $all_methods != 1}
                <div class="btn-center"><a href="{Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $orders_id, 'all_methods' => '1'])}" class="btn btn-cancel-foot popup2">Show All</a></div>
        {/if}
                </div>
            </form>
    {/if}
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
function selectMethod() {
    var params = $('#select_method_form').serialize();
    $('.pop-up-content:last').html('<div class="preloader"></div>');
    $.get("{Yii::$app->urlManager->createUrl('orders/print-label')}", params, function(data, status) {
        if (status == "success") {
            $('.pop-up-content:last').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function cancelStatement() {
    $('.pop-up-close:last').trigger('click');
    return false;
}
$(document).ready(function() { 
    $('a.popup2').popUp();
    var formLabel = $('#select_method_form');
    $('input[name="method"]', formLabel).on('change', function (e) {
        $('.labelWidget').hide();
        $(this).closest('.labelWrap').find('.labelWidget').show();
    });
    var methods = $('input[name="method"]', formLabel);
    if (methods.length === 1) {
        methods.prop("checked", true);
        methods.change();
    }
    {if $orders_id}
        $('.pop-up-close:last').on('click', function () {
            $.get("{\Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id])}", function(data) { $("#order_management_data").html(data.content); },"json");
        });
    {/if}
});
</script>
