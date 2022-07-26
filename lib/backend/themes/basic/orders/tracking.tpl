<div id="trackingNumber">
    <form name="savetrack" method="post" onSubmit="return saveTracking();">
    <div class="trackingBox">
        {if $trackings|count > 0}
            {foreach $trackings as $tracking_number}
                {assign var="tracking_data" value=\common\helpers\Order::parse_tracking_number($tracking_number)}
                <div class="row js-tracking-row">
                    <div><input name="tracking_number[]" type="hidden" value="{$tracking_number}" class="form-control js-tracking_code"></div>
                    <div class="t_number js-tracking-show">{$tracking_data['number']}</div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-2"><span class="edit-pt"><i class="icon-pencil"></i></span></div>
                        <div class="col-md-2"><span class="remove-pt"><i class="icon-remove minus_ballance"></i></span></div>
                        <div class="col-md-4"></div>
                    </div>
                    <a href="{$tracking_data['url']}" class="js-tracking-url-link" target="_blank"><img class="js-tracking-qr" src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$order_id}&cID={$customers_id}&tracking=1&tracking_number={$tracking_data['number']}"></a>
                </div>
            {/foreach}
        {else}
            <div class="row">
            <input name="tracking_number[]" type="text" value="" class="form-control">
            </div>
        {/if}
    </div>
        <center>
        <a href="javascript:void(0)" onclick="return addMore();" class="btn">{$smarty.const.TEXT_ADD_MORE}</a>
        </center>
        <input type="hidden" name="orders_id" value="{$order_id}">
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
    function saveTracking(){
        $(window).scrollTop(0);
        $.post("{$app->urlManager->createUrl('orders/savetracking')}", $('form[name=savetrack]').serialize(), function(data, status){
            if (status == "success") {
              getTrackingList();
              setTimeout(function(){
                closePopup();
              },500);
            } else {
                alert("Request error.");
            }
        },"json");
        return false;                              
    }
    function addMore(){    
        $('.trackingBox').append('<div class="row"><input name="tracking_number[]" type="text" value="" class="form-control"></div>');
    }
    
    $('#trackingNumber').on('click', '.edit-pt, .remove-pt', function(){
        var $btn = $(this);
        var $container = $(this).parents('.js-tracking-row');
        if ( $btn.hasClass('remove-pt') ) {
            $container.remove();
            if ( $('.trackingBox .js-tracking-row').length==0 ) {
                addMore();
            }
            return false;
        }
        var $input = $container.find('input.js-tracking_code').first();
        var $tracking_show = $container.find('.js-tracking-show');
        var img = $container.find('img.js-tracking-qr');
        
        if ($btn.hasClass('btn')){
            // { old tracking code
            $tracking_show.html($input.val()).show();
            $input.attr('type', 'hidden').hide();
            if($input.val().length > 0){
                $(img).attr('src', '{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-qrcode?oID={$order_id}&cID={$customers_id}&tracking=1&tracking_number='+$input.val());
            } else {
                $(img).attr('src', '');
            }
            // } old tracking code
            if ( $input.val() ) {
                $.post("{$app->urlManager->createUrl('orders/parse-tracking')}", { tracking_number: $input.val(), order_id:{$order_id|intval} }, function (data) {
                    if (data.tracking) {
                        var show_tracking = data.tracking.number;
                        if ( data.tracking.carrier ){
                            show_tracking = data.tracking.carrier+' '+data.tracking.number
                        }
                        $tracking_show.html(show_tracking).show();
                        $container.find('.js-tracking-url-link').attr('href', data.tracking.url);
                    }
                    if ( typeof data.qr_image_src !=='undefined') {
                        $(img).attr('src', data.qr_image_src);
                    }
                });
            }
            $btn.removeClass('btn');
        } else {
            $btn.addClass('btn');
            $tracking_show.hide();
            $input.attr('type', 'text').show();
        }
        return false;
    })
</script>