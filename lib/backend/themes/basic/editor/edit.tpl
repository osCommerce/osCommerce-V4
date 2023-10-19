{use class="yii\helpers\Html"}
{\backend\components\Currencies::widget(['currency' => $manager->get('currency')])}
{\backend\assets\OrderAsset::register($this)|void}

{Html::hiddenInput('currentCart', $currentCart)}

<div class="order-helpers">
    <div class="btn-bar btn-bar-top after">
        <div class="btn-left">
            <span onclick="return backStatement();" class="btn btn-back" title="{$smarty.const.IMAGE_BACK}">{$smarty.const.IMAGE_BACK}</span>
        </div>
        <div class="btn-right">
            <span onclick="return saveCheckoutForm();" id="save_checkout" class="btn btn-primary btn-save-checkout" title="{$smarty.const.IMAGE_SAVE}">{$smarty.const.IMAGE_SAVE}</span>
            {if \common\helpers\Extensions::isAllowed('UpdateAndPay')}
                <button class="btn btn-right btn-confirm btn-update-pay" data-class="popup-update-pay" onclick="return updatePay(this);" title="{$smarty.const.IMAGE_UPDATE_PAY}"{if !$order_state['UpdateAndPay_available']} style="display: none"{/if}>{$smarty.const.IMAGE_UPDATE_PAY}</button>
                <script type="text/javascript">
                    $(document).on('ajaxComplete', function( event, xhr, settings ) {
                        if (xhr && xhr.responseJSON && xhr.responseJSON.order_state) {
                            if (xhr.responseJSON.order_state.UpdateAndPay_available === true) {
                                $('.btn-update-pay').show()
                            } else if (xhr.responseJSON.order_state.UpdateAndPay_available === false) {
                                $('.btn-update-pay').hide()
                            }
                        }
                    } );
                </script>
            {/if}
            {$manager->render('DeleteOrder', ['manager'=> $manager])}
            {$manager->render('AdminCarts', ['manager'=> $manager, 'admin' => $admin])}
            {$manager->render('OrderStatuses', ['manager'=> $manager, 'admin' => $admin])}
            {$manager->render('PlatformDetails', ['manager'=> $manager, 'admin' => $admin])}

            {if $paramOrderId}
                <a href="{$app->urlManager->createUrl(['orders/order-history', 'orders_id' => $paramOrderId])}" class="btn btn-link-legend popup" data-class="legend-info" title="{$smarty.const.TEXT_ORDER_LEGEND}">{$smarty.const.TEXT_ORDER_LEGEND}</a>
                {*<span onclick="return deleteOrder({$paramOrderId});" class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</span>*}
            {/if}
        </div>
    </div>

</div>

<div id="message">
{$message|default:null}
</div>

<div class="order-content">

    <div class="row">
        <div class="col-12 col-xl-8">

            <div class="products-listing-table mb-3">
                {$manager->render('ProductsListing', ['manager' => $manager])}
            </div>

            {$manager->render('Contact', ['manager' => $manager, 'admin'=> $admin])}

        </div>
        <div class="col-12 col-xl-4">
            {$manager->render('OrderTotals', ['manager' => $manager])}
        </div>
    </div>






    {if $manager->showSettings|default:null}
        <div class="order-content-disabled product-frontend disable">
            {Html::a('', ['editor/settings', 'currentCurrent' => $currentCart, 'back' => $app->controller->view->backOptionTrue], ['class' => 'popup order-settings', 'data-class' => 'order-settings-box', 'style'=>'display:none;'])}
        </div>
    {/if}
    {if $manager->showAdminOwnerNotification}
        {if $currentCart}
        <div class="order-content-disabled product-frontend disable">
            {Html::a('', \yii\helpers\Url::to(['editor/owner', 'currentCurrent' => $currentCart]), ['class' => 'popup admin-owner', 'data-class' => 'admin-owner-box', 'style'=>'display:none;'])}
        </div>
        {/if}
    {/if}
</div>



<!--=== Page Content ===-->
<input type="hidden" name="admin_message" value="{if strlen($admin_message|default:null) > 0}1{else}0{/if}">

<script type="text/javascript">
    
    $(document).ajaxComplete(function( event, jqxhr, settings, thrownError ) {
        if ( jqxhr.status && jqxhr.status == 406 ){
            //$(document).trigger('not_acceptable');
            window.location.reload();
        }
    });
    
    var $urlCalculateRow = "{Yii::$app->urlManager->createUrl(array_merge(['editor/cart-worker'], Yii::$app->request->getQueryParams()))}";


    var bodyTag = $('body');
    var rates = [];
    {if $taxRates|default:null|is_array}
        {foreach $taxRates as $key => $rate}
            {if $rate==''}{$rate='0'}{/if}
            rates['{$key}'] = '{$rate}';
        {/foreach}
    {/if}
    {if $isEditInGrid|default:null}
        bodyTag.on('keyup','input[name="final_price"]',function(){
            var parentSelector = $(this).closest('tr.dataTableRow');
            var tax = $('select.tax',parentSelector).val();
            var qty = parseInt($('input.qty',parentSelector).val());
            var finalPrice = parseFloat($(this).val());
            var finalPriceTotal = finalPrice * qty;
            var finalPriceTotalInc = (finalPrice + getTaxCoefficient(finalPrice,tax)) * qty;
            $('input[name="final_price_total"]',parentSelector).val(finalPriceTotal.toFixed(6));
            $('input[name="final_price_total_inc_tax"]',parentSelector).val(finalPriceTotalInc.toFixed(6));
        });

        bodyTag.on('keyup','input[name="final_price_total"]',function(){
            var parentSelector = $(this).closest('tr.dataTableRow');
            var tax = $('select.tax',parentSelector).val();
            var qty = parseInt($('input.qty',parentSelector).val());
            var finalPriceTotal = parseFloat($(this).val());
            var finalPrice = finalPriceTotal / qty;
            var finalPriceTotalInc = (finalPrice + getTaxCoefficient(finalPrice,tax)) * qty;
            $('input[name="final_price"]',parentSelector).val(finalPrice.toFixed(6));
            $('input[name="final_price_total_inc_tax"]',parentSelector).val(finalPriceTotalInc.toFixed(6));
        });

        bodyTag.on('keyup','input[name="final_price_total_inc_tax"]',function(){
            var parentSelector = $(this).closest('tr.dataTableRow');
            var tax = $('select.tax',parentSelector).val();
            var qty = parseInt($('input.qty',parentSelector).val());
            var finalPriceTotalInc = parseFloat($(this).val());
            var finalPrice = getUnTaxCoefficient(finalPriceTotalInc / qty,tax) ;
            var finalPriceTotal = finalPrice * qty;
            $('input[name="final_price"]',parentSelector).val(finalPrice.toFixed(6));
            $('input[name="final_price_total"]',parentSelector).val(finalPriceTotal.toFixed(6));
        });
        bodyTag.on('blur','input[name="final_price_total"], input[name="final_price_total_inc_tax"], input[name="final_price"]',function () {
            updateProduct(this,['price'])
        });
        bodyTag.on('blur','input[name="name"]',function () {
            updateProduct(this,['name'])
        });
        function onlyUnique(value, index, self) {
            return self.indexOf(value) === index;
        }
    {/if}
    function getTaxCoefficient(price,tax)
    {
        var value = 0;
        if (rates.hasOwnProperty(tax)){
            value = (price * parseFloat(rates[tax]) / 100);
        }
        return value;
    }
    function getUnTaxCoefficient(price,tax)
    {
        var value = price;
        if (rates.hasOwnProperty(tax)){
            value = (price / (100 + parseFloat(rates[tax])) * 100);
        }
        return value;
    }
    function updateProduct(obj,params) {
        
        var postData = {
            'action': 'add_product',
            'currentCart': $('input[name=currentCart]').val(),
            'uprid' :  encodeURIComponent($(obj).parents('.product_info').find('input[name=uprid]').val()),
            'products_id': $(obj).parents('.product_info').find('input[name=products_id]').val(),
            'qty': $(obj).parents('.product_info').find('.qty').val(),
            'tax' : $(obj).parents('.product_info').find('.tax').val(),
            'gift_wrap':$(obj).parents('.product_info').find('.gift_wrap').prop('checked')
        }
        {if $isEditInGrid|default:null}
            if( Array.isArray(params) && params.length > 0 ){
                params = params.filter( onlyUnique );
                params.forEach(function(param, i, arr) {
                    switch(param){
                        case 'name':
                            postData.name = $(obj).parents('.product_info').find('.name').val();
                            break;
                        case 'price':
                            postData.final_price = $(obj).parents('.product_info').find('.final_price').val();
                            break;
                    }
                });
            }
        {/if}
        $.post("{Yii::$app->urlManager->createUrl('orders/addproduct')}?orders_id={$oID|default:null}", postData, function(data, status){
            if (status == "success") {
                $('#shiping_holder').html(data.shipping_details);
                $('#products_holder').html(data.products_details);
                $('#totals_holder').html(data.order_total_details);
                $('#totals_holder .mask-money').setMaskMoney();
                $('#message').html(data.message);
                setPlugin();
                localStorage.orderChanged = true;
            } else {
                alert("Request error.");
            }
        },"json");
    }

function updatePay(obj) {
    order.updatePay(obj);
    return false;
}

function deleteOrderProduct(obj) {    
    order.removeProduct(obj, 'remove_product', setPlugin);
}

function deleteOrderGiveaway(obj) {    
    order.removeProduct(obj, 'remove_giveaway', setPlugin);
}

function savePaid(form){
    order.savePaid(form, setPlugin);
    return false;
}


function checkproducts(products){
    var success = true;
    if (products.length > 0){
        products.forEach(function(e){
            success = e.product.checkAttributes() && e.product.checkQty();
        });
    }    
    if (!success){
       bootbox.dialog({
        message: '<div class=""><label class="control-label">'+"{$smarty.const.ERROR_WARNING}"+'</label></div>',
        title: "{$smarty.const.ICON_ERROR}",
          buttons: {
            cancel: {
              label: "{$smarty.const.TEXT_BTN_OK}",
              className: "btn-cancel",
              callback: function() {
                }
            }
          }
      });

    } else {
        if (typeof unformatMaskMoney == 'function') {
            unformatMaskMoney();
        }        
    }
    return success;
}

function checkAdmin(){
    if ($('input[name=admin_message]').val() == 1){
        bootbox.dialog({
                    closeButton: false,
                    message: "{$admin_message|default:null}",
                    title: "{$smarty.const.ICON_WARNING}",
                    buttons: {
                            success: {
                                    label: "{$smarty.const.TEXT_BTN_YES}",
                                    className: "btn-delete",
                                    callback: function() {
                                        $.post("{$app->urlManager->createUrl('orders/reset-admin')}", {
                                            'basket_id': "{$cart->basketID|default:null}",
                                            'customer_id': "{$cart->customer_id|default:null}",
                                            'orders_id': "{$oID|default:null}",
                                        }, function(data, status){
                                            if (status == "success") {
                                                window.location.href= data.reload;
                                            } else {
                                                alert("Request error.");
                                            }
                                        },"json");
                                    }
                            },
                            main: {
                                    label: "{$smarty.const.TEXT_BTN_NO}",
                                    className: "btn-cancel",
                                    callback: function() {
                                        window.location.href = "{$app->urlManager->createUrl('orders/')}";
                                    }
                            }
                    }
            });       
    }
}

function deleteOrder() {
        bootbox.dialog({
                message: "{$smarty.const.TEXT_INFO_DELETE_INTRO}",
                title: "{$smarty.const.TEXT_INFO_HEADING_DELETE_ORDER}",
                buttons: {
                        success: {
                                label: "{$smarty.const.TEXT_BTN_YES}",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("{$app->urlManager->createUrl('orders/orderdelete')}", {
                                        'orders_id': "{$oID|default:null}",
                                    }, function(data, status){
                                        if (status == "success") {
                                            $("#order_management_data").html('');
                                            window.location.href= "{$app->urlManager->createUrl('orders/')}";
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "{$smarty.const.TEXT_BTN_NO}",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
    return false;
}
function changeActionType() {
    var subaction = document.createElement('input');
    subaction.name='subaction';
    subaction.type='hidden';
    subaction.value='return';
    document.create_order.appendChild(subaction);
    return true;
}
function closePopup() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
}
function billingAddressHasBeenChanged() {
    $('#update_billing_address_box').show();
    //orderHasBeenChanged();
}
function billingAddressNotChanged() {
    $('#update_billing_address_box').hide();
    $('input[name="update_billing_address"]').prop('checked', false);
    //orderHasBeenChanged();
}
function deliveryAddressHasBeenChanged() {
    $('#update_delivery_address_box').show();
    //orderHasBeenChanged();
}
function deliveryAddressNotChanged() {
    $('#update_delivery_address_box').hide();
    $('input[name="update_delivery_address"]').prop('checked', false);
    //orderHasBeenChanged();
}
function orderHasBeenChanged() {
	if (typeof unformatMaskMoney == 'function') {
		unformatMaskMoney();
	}
	$.post('orders/order-edit' + ($('input[name=oID]').val().length>0?'?orders_id='+$('input[name=oID]').val():''), 
		$('#edit_order').serialize(),
	function (data, status){
		$('#address_details').html(data.address_details);
		$('#shiping_holder').html(data.shipping_details);
		$('#payment_holder').html(data.payment_details);
		$('#products_holder').html(data.products_details);
		$('#totals_holder').html(data.order_total_details);
		$('#order_statuses').html(data.order_statuses);
		$('#totals_holder .mask-money').setMaskMoney();
		$('#message').html(data.message);
        setDataTables();
		localStorage.orderChanged = true;
		setPlugin();
	}, 'json');
}
function backStatement() {
{if $app->controller->view->newOrder}
    {if $app->controller->view->backOption == 'orders'}
        window.location.href="{$app->urlManager->createUrl('orders/')}";
    {/if}
    {if $app->controller->view->backOption == 'customers'}
        window.location.href="{$app->urlManager->createUrl('customers/')}";
    {/if}
{else}    
    window.history.back();
{/if}        
    return false;
}
function resetStatement(id) {
    $('#cancel_button').hide();
    $.post("{$app->urlManager->createUrl('orders/order-edit')}", {
        'orders_id': id,
    }, function (data, status) {
        if (status == "success") {  
            $("#order_management_data").html(data);
            $('.datatable').DataTable( {
                "scrollY":        "200px",
                "scrollCollapse": true,
                "paging":         false
            } );
        }
    }, "html");
    return false;
}

/*function addModule(code, visible){
	var params = {};
    params.currentcart = $('input[name=currentCart]').val();
	if (code.length < 1) return;
	params.update_totals = {};
	if (typeof unformatMaskMoney == 'function') {
		unformatMaskMoney();
	}	
	$.each($('input[name*=update_totals].use-recalculation'), function (i,e){
		if (!params.update_totals.hasOwnProperty($(e).data('control').substr(1))) params.update_totals[$(e).data('control').substr(1)] = {};
		params.update_totals[$(e).data('control').substr(1)].in = $('input[name="update_totals['+$(e).data('control').substr(1)+'][in]"]').val();
		params.update_totals[$(e).data('control').substr(1)].ex = $('input[name="update_totals['+$(e).data('control').substr(1)+'][ex]"]').val();
	});
	
	if (typeof code != 'undefined' && code.length > 0){
		params.action = 'new_module';		
		if (visible){
            if (Array.isArray(code)){
                $.each(code, function(i,e){
                  if (code == '$ot_custom'){
                    params.update_totals_custom = {};
                        params.update_totals_custom['prefix'] = $('select[name="update_totals_custom[prefix]"]').val();
                        params.update_totals_custom['desc'] = $('input[name="update_totals_custom[desc]"]').val();
                  } else {
                      params.update_totals[e] = '&nbsp;'; 
                  }                          
                });
            } else {
                params.update_totals[code] = '&nbsp;';
            }                  
        }
	}	
        
	$.post('orders/order-edit?orders_id={$oID|default:null}',
		params
	, function(data, status){
		$('#totals_holder').html(data.order_total_details);
		$('#totals_holder .mask-money').setMaskMoney();
	}, 'json');
}*/

function removeModule(code, coupon){
    if (coupon.length > 0) {
        order.removeCouponCode(code, coupon);
    } else {
        order.removeModule(code);
    }
}

function setDataTables(){
$('.datatable').DataTable( {
					"scrollY":        "200px",
					"scrollCollapse": true,
					"paging":         false
				} );
}

var user_work = false;
var tout;

let openedDropdown = '';

function setPlugin(data){
    $('a.popup').off().popUp({
		box_class: $(this).data('class'),
        //data:{ 'currentCart' : $('input[name=currentCart]').val() }
	});
    
    {if $manager->showSettings|default:null}
       $('.order-settings').trigger('click');
    {/if}
    
    {if $manager->showAdminOwnerNotification}
        $('.admin-owner').trigger('click');
    {/if}
    
    $('.spinner-percent').off().on('change', function(e, ui){
        //order.getExtraCharge(e.target, 'extra_charge');
        //$(this).val(parseInt($(this).val())+'%');

    }).spinner({
        step: 1,
        min:0,
        max:100,
        start: function( event, ui ) {
            $(this).val(parseInt($(this).val()));
        },
        stop: function(e, ui){
            order.getExtraCharge(e.target, 'extra_charge');
            $(this).val(parseInt($(this).val())+'%');
            
        }
    })
    
    $('.spinner-fixed').off().on('change', function(e, ui){
        order.getExtraCharge(e.target, 'extra_charge');
        $(e.taget).setMaskMoney();
    }).spinner({
        step: 0.01,
        min:0,
        start: function( event, ui ) {
            unformatMaskMoney('.spinner-fixed');
        },
        stop: function(e, ui){
            order.getExtraCharge(e.target, 'extra_charge');
            $(e.taget).setMaskMoney();
        }
    })
     
    $('.result-price').setMaskMoney();
     
    $('.action-percent, .action-fixed').off().change(function(){
        order.getExtraCharge(this, 'extra_charge');
    });

    $('.overwritten-choose').each(overwrittenChoose)
    $('.overwritten-choose').on('change', overwrittenChoose)

    function overwrittenChoose(){
        $(this).closest('.price-editing').find('.extra-disc-box').hide();
        $(this).closest('.price-editing').find('.overwritten-' + $(this).val()).show()
    }

    $('.dropdown').off('show.bs.dropdown').on('show.bs.dropdown', function () {
        openedDropdown = $(this).data('id');
    })
    $('.dropdown').off('hidden.bs.dropdown').on('hidden.bs.dropdown', function () {
        openedDropdown = '';
    });

    if (openedDropdown) {
        $(`.dropdown[data-id="${ openedDropdown }"] .price-edit`).trigger('click')
    }

    /*$(".check_on_off").off().bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
    });*/
    
    $('.btn-save-cart').off().click(function(e){
        e.preventDefault();
        order.saveCart();
    })
    
}

function preloadCurrentCart(){
    var url = window.location.href.substr(0, window.location.href.length- window.location.hash.length);
    if (url.indexOf('currentCart') == -1){
        if (url.indexOf('?') != -1){    
            url = url + '&currentCart=' + $('input[name=currentCart]').val();
        } else {
            url = url + '?currentCart=' + $('input[name=currentCart]').val();
        }
        url = url + window.location.hash;
        window.history.replaceState({ }, '', url);
        window.location.reload();
    }
}

$(document).ready(function() { 
	
    order.activate_plus_minus('.products-listing-table');
    
    $(".update_pay").popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-update-pay'><div class='popup-heading up-head'>{$smarty.const.IMAGE_UPDATE_PAY}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });	
	
    $('#reset-cart').click(function(){
        order.resetCart();
    })
    //order.activate_plus_minus('.edit_product_popup');	
	
	localStorage.orderChanged = true;
    
    checkAdmin();
    
    preloadCurrentCart();
      		
    $(window).resize(function () {
        setTimeout(function () {
            var height_1 = $('.wb-or-ship1').height();
            var height_2 = $('.wb-or-pay1').height();
            if(height_1 > height_2){
                $('.wb-or-pay1').css('min-height', height_1);
            }else{
                $('.wb-or-ship1').css('min-height', height_2);
            }
        }, 800);
        $('.widget-collapse-height').click(function(){ 
            setTimeout(function () {
                var height_1 = $('.wb-or-ship1').height();
                var height_2 = $('.wb-or-pay1').height();
                if(height_1 > height_2){
                    $('.wb-or-pay1').css('min-height', height_1);
                }else{
                    $('.wb-or-ship1').css('min-height', height_2);
                }
            }, 800);
        });
    })
    $(window).resize(); 
});

</script>
<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
