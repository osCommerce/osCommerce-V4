{\backend\components\Currencies::widget(['currency' => $manager->get('currency')])}
<div class="gridBg contentContainer">
    <div class="btn-bar btn-bar-top sm-1500">
        <div class="btn-left">
            <form action="{$app->urlManager->createUrl('tmp-orders/process-order')}" method="get" class="go-to-order" style="margin-left: 0px">
            {$smarty.const.TEXT_GO_TO_ORDER} <input type="text" class="form-control" name="orders_id"/> <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_GO}</button>
            </form>
        </div>
        <div class="btn-right">
            {if empty($child_id) && \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}
            <a href="javascript:void(0)" onclick="return convertOrder({$order->order_id});" class="btn btn-danger" title="{$smarty.const.IMAGE_CONVERT}">
                <i class="icon-file-text"></i>
                <span class="title">{$smarty.const.IMAGE_CONVERT}</span>
            </a>
            {/if}
            {if !empty($child_id)}
                <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $child_id])}" class="btn " title="{$smarty.const.TEXT_CONVERTED_ORDER|escape}{$child_id}">
                    <i class="icon-file-text"></i>
                    <span class="title">{$smarty.const.TEXT_CONVERTED_ORDER}{$child_id}</span>
                </a>
            {/if}

            <a href="{$app->urlManager->createUrl(['tmp-orders/order-history', 'orders_id' => $order->order_id, 'cid' => $customer_id])}" class="btn btn-legend" title="{$smarty.const.TEXT_ORDER_LEGEND}">
                <i class="osci-order-legend"></i>
                <span class="title">{$smarty.const.TEXT_ORDER_LEGEND}</span>
            </a>

            <span class="print-button btn" onclick="printDiv()" title="{$smarty.const.TEXT_PRINT}">
                <i class="osci-print"></i>
                <span class="title">{$smarty.const.TEXT_PRINT}</span>
            </span>

            {foreach \common\helpers\Hooks::getList('tmp-orders/process-order', 'btn-bar-right') as $file}
                {include file=$file}
            {/foreach}

            {if $fraudView}{$fraudView->head()}{/if}

            {if empty($child_id) && \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}
            <a href="javascript:void(0)" onclick="return deleteOrder({$order->order_id});" class="btn btn-danger" title="{$smarty.const.IMAGE_DELETE}">
                <i class="osci-delete"></i>
                <span class="title">{$smarty.const.IMAGE_DELETE}</span>
            </a>
            {/if}

        </div>
        {foreach \common\helpers\Hooks::getList('tmp-orders/process-order', 'btn-bar-top') as $filename}
            {include file=$filename}
        {/foreach}
    </div>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<!--=== Page Content ===-->
<link href="{{$smarty.const.DIR_WS_ADMIN}}/plugins/fancybox/fancybox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$smarty.const.DIR_WS_ADMIN}/plugins/fancybox/jquery.fancybox.pack.js"></script>

<!--===Process Order ===-->
<div class="row w-or-prev-next">
    {if $app->controller->view->order_prev > 0}
    <a href="{$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $app->controller->view->order_prev])}" class="btn-next-prev-or btn-prev-or" title="{$smarty.const.TEXT_GO_PREV_ORDER} (#{$app->controller->view->order_prev})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_PREV_ORDER}"></a>
    {/if}
    {if $app->controller->view->order_next > 0}
    <a href="{$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $app->controller->view->order_next])}" class="btn-next-prev-or btn-next-or" title="{$smarty.const.TEXT_GO_NEXT_ORDER} (#{$app->controller->view->order_next})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_NEXT_ORDER}"></a>
    {/if}
    <div class="col-md-12" id="order_management_data">
        {include "process-order.tpl"}
    </div>
</div>
<!-- Process Order -->
<script type="text/javascript">

function check_form() {
    return false;
}
function resetStatement() {
     window.history.back();
    return false;
}
function closePopup() {
    $('.popup-box').trigger('popup.close');
    $('.popup-box-wrap').remove();
    return false;
}

function wrap(data){
    let _context = '';
    if (Array.isArray(data)){
        $.each(data, function(i, e){
            _context = _context + "<br/><div class='alert fade in alert-"+e.type+"'><span id='message_plce'>"+e.text+"</span></div>";
        })
    } else {
        _context = "<br/><div class='alert fade in alert-"+data.type+"'><span id='message_plce'>"+data.text+"</span></div>";
    }
    return _context;
}

$(document).ready(function() {

    const prevNextTop = $('.w-or-prev-next').offset().top;
    const $btnNextPrev = $('.btn-next-prev-or');
    btnNextPrevPosition();
    $(window).on('scroll resize', btnNextPrevPosition)
    function btnNextPrevPosition(){
        let top = $(window).height() / 2 + $(window).scrollTop() - prevNextTop;
        $btnNextPrev.css('top', top)
    }

    $('a.btn-legend, .btn-history').popUp({
        box_class:'legend-info'
    });


    $('.fancybox').fancybox({
      nextEffect: 'fade',
      prevEffect: 'fade',
      padding: 10
    });

	$('body').on('click', '.fancybox-wrap', function(){
		$.fancybox.close();
	})

    $('.ajax-submit').on('click', function(){
        $.post($(this).attr('href'), $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            dialog = bootbox.dialog({
                message: wrap(data),
                buttons: {
                    ok: {
                        label: "{$smarty.const.TEXT_BTN_OK}",
                        className: 'btn-info',
                        callback: function(){
                        }
                    }
                }
            });

            //$("#order_management_data").html(data);
        } else {
            alert("Request error.");
        }
    },"json");
	return false;
    });

    $('body').addClass('process-order-page')
});

function printDiv() {
    window.print();
    window.close();
}


function deleteOrder(orders_id) {
    const buttons = {
        success: {
            label: '{$smarty.const.TEXT_DELETE_AND_TO_LIST|escape}',
            className: "btn-delete",
            callback: function () {
                $.post("{\Yii::$app->urlManager->createUrl('tmp-orders/orderdelete')}", {
                    'orders_id': orders_id
                }, function (data, status) {
                    if (status == "success") {
                        $("#order_management_data").html('');
                        window.location.href = "{\Yii::$app->urlManager->createUrl('tmp-orders/')}";
                    } else {
                        alert("Request error.");
                    }
                }, "html");
            }
        }
    };

    if (!$('.btn-next-prev-or.btn-next-or').hasClass('btn-next-prev-or-dis')) {
        buttons.ok = {
            label: '{$smarty.const.TEXT_DELETE_AND_TO_NEXT|escape}',
            className: "btn-delete",
            callback: function () {
                $.post("{\Yii::$app->urlManager->createUrl('tmp-orders/orderdelete')}", {
                    'orders_id': orders_id
                }, function (data, status) {
                    if (status == "success") {
                        $("#order_management_data").html('');
                        window.location.href = $('.btn-next-prev-or.btn-next-or').attr('href');
                    } else {
                        alert("Request error.");
                    }
                }, "html");
            }
        };
    }
    buttons.cancel = {
        label: '{$smarty.const.IMAGE_CANCEL|escape}',
        className: "btn-cancel",
    };

    bootbox.dialog({
        message: '{$smarty.const.TEXT_INFO_DELETE_INTRO}<br />',
        title: "{$smarty.const.TEXT_INFO_HEADING_DELETE_ORDER}",
        buttons: buttons
    });
    return false;
}


function convertOrder(orders_id) {
    const buttons = { };
    buttons.ok = {
        label: '{$smarty.const.TEXT_OK|escape}',
        className: "btn-ok",
        callback: function () {
            $.post("{\Yii::$app->urlManager->createUrl('tmp-orders/convert')}", {
                'orders_id': orders_id, 'current_date': $('.bootbox-body .current_date:checked').val()
            }, function (data, status) {
                if (status == "success") {
                    if (data.error) {
                        bootbox.alert(data.msg);
                    } else {
                        if (data.url) {
                            window.location.href = data.url;
                        } else {
                            window.location.reload();
                        }
                    }
                } else {
                    alert("Request error.");
                }
            }, "json");
        }
    };
    buttons.cancel = {
        label: '{$smarty.const.IMAGE_CANCEL|escape}',
        className: "btn-cancel",
    };

    bootbox.dialog({
        title: "{$smarty.const.TEXT_INFO_HEADING_CONVERT_TMP_ORDER|escape}",
        message: '{$smarty.const.TEXT_INFO_CONVERT_INTRO|escape}'
    + '<br><label><input name="current_date" class="current_date" type="radio" value=1 checked>{$smarty.const.TEXT_CURRENT_DATE_TIME|escape}</label>'
    + '<br><label><input name="current_date" class="current_date" type="radio" value=0>{$smarty.const.TEXT_TMP_ORDER_DATE_TIME|escape}</label>'

    ,
        buttons: buttons
    });
    return false;
}

</script>
<style>
@media print {
a[href]:after {
   content:"" !important;
}
#content, #container, #container > #content > .container{
	margin:0 !important;
}
#sidebar, header, .btn-bar, .top_header, .pra-sub-box .pra-sub-box-map:nth-child(2), .btn-next-prev-or, .btn-next-prev-or.btn-next-or, .footer{
	display:none !important;
}
.pr-add-det-box.pr-add-det-box02.pr-add-det-box03 .pra-sub-box-map{
	width:100%;
}
.pr-add-det-box.pr-add-det-box03 .pra-sub-box-map .barcode{
margin-top:-132px !important;
}
.box-or-prod-wrap{
padding:0 !important;
}
.filter-wrapp{
display:none;
}
}
</style>
        <!-- /Page Content -->
</div>