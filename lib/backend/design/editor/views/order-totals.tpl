{use class="Yii"}
{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<div class="order_totals widget box">
    <div class="widget-header"><h4>{$smarty.const.ORDER_SUMMARY}</h4></div>

	<table class="p-or-t-tab">
        <tr class="p-or-t-tab-row">
            <td></td>
            <td align="right"><strong>{$smarty.const.TEXT_EXC_VAT}</strong></td>
            <td align="right"><strong>{$smarty.const.TEXT_INC_VAT}</strong></td>
            <td class="totals-adjust"></td>
        </tr>
        {if is_array($lines)}
            {foreach $lines  as $line}
            <tr class="p-or-t-tab-row total-row">
                <td><strong>{$line[0]}</strong></td>
                <td><b>{$line[1]}</b></td>
                <td><b>{$line[2]}</b></td>
                <td class="totals-adjust">{$line[3]}</td>
            </tr>
            {/foreach}
        {/if}
    </table>
    <div class="btn-tools-box">            
        <button class="btn totals_reset">{$smarty.const.TEXT_RESET_RECALCULATION}</button>

        {if \common\helpers\Acl::rule(['ACL_ORDER', 'HEADER_ADD_TOTAL_ELEMENT'])}
            <button class="btn add-more">{$smarty.const.HEADER_ADD_TOTAL_ELEMENT}</button>
        {/if}

    </div>
    <div style="display:none;" class="save-paid-box">
        <div style="padding:5px;">
            <label>{$smarty.const.TEXT_PAID_AMOUNT} ({$currency})</label>
            <div class="paid-amount-box">
            {Html::dropDownList('paid_prefix', '+', ['+' => '+', '-' => '-'], ['class' => 'form-select'])}
            {Html::textInput('paid_amount', number_format($proposeToPaid,2,'.',''), ['class' => 'form-control mask-money'])}
            </div>
            <label>{$smarty.const.TABLE_HEADING_COMMENTS}</label>
            <textarea name="comment" class="form-control"></textarea>
            <input type="hidden" name="action" value="update_amount">
        </div>
    </div>

<script>
    {$js}   
    var $urlCheckout = '{$urlCheckout}';
	(function($){
		$('.totals_reset').off().click(function(e){
            e.preventDefault();
                bootbox.dialog({
                    message: '<div class=""><label class="control-label">'+"{$smarty.const.TEXT_CONFIRM_RESET_TOTALS}"+'</label></div>',
                    title: "{$smarty.const.ICON_WARNING}",
                      buttons: {
                        cancel: {
                          label: "{$smarty.const.TEXT_BTN_NO}",
                          className: "btn-cancel btn-left",
                        },
                        success: {
                            label: "{$smarty.const.TEXT_BTN_YES}",
                            className: "btn-primary",
                            callback: function() {
                                order.resetTotals();
                            }
                        },
                      }
                });
        });
            
        $('body').off().on('click', '.totals.edit-pt', function(){
            if ($(this).hasClass('btn')){
                $(this).removeClass('btn');
                var control = $(this).parents('tr').find('input.use-recalculation:first').data('control');
                order.recalculateTotals(control, $(this).parents('.order_totals'), setPlugin);
                //addModule(control);
				//localStorage.orderChanged = true;
            } else {
                $(this).addClass('btn');
                var elements = $(this).parents('tr').find('input.use-recalculation');
                $.each(elements, function(i,e){
                    $(e).attr('type','input');
                    if ($(e).data('control') != 'ot_bonus_points'){
                        $(e).setMaskMoney();
                    }
                    $(e).next().hide();
                });
                $('.use-recalculation:visible').focusout(function(){
                    var control = $(this).data('control');
                    order.recalculateTotals(control, $(this).parents('.order_totals'), setPlugin);
                });
            }
        });
        
        $('body').on('click', '.edit-paid', function(e){
            e.preventDefault();
            var paid = this;
            $(paid).popUp({
              data: $('.save-paid-box:first').html(),
              event: 'show',
              only_show: true,
              box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading add-total-element'>{$smarty.const.TEXT_UPDATE_PAID_AMOUNT}</div><div class='pop-up-close'></div><form action='' class='form-paid' method='post' onSubmit='return savePaid(this);'><div class='pop-up-content'></div><div class='noti-btn'><div><button class='btn btn-cancel'>{$smarty.const.IMAGE_CANCEL}</button></div><div><input type='submit' class='btn btn-confirm save-amount' value='{$smarty.const.IMAGE_SAVE}'></div></div></form></div></div>",
           });
        })
        

        $('.add-more').click(function(e){
            var add = this;
            $(add).popUp({
              data: list_modules,
              event: 'show',
              only_show: true,
              box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading add-total-element'>{$smarty.const.HEADER_ADD_TOTAL_ELEMENT}</div><div class='pop-up-close'></div><form action='' class='add-new-modules' method='post'><div class='pop-up-content'>"+list_modules+"</div><div class='noti-btn'><div><button class='btn btn-cancel'>{$smarty.const.IMAGE_CANCEL}</button></div><div><input type='submit' class='btn btn-confirm' value='{$smarty.const.IMAGE_ADD}'></div></div></form></div></div>",
           });
           
           $('body').on('submit', '.add-new-modules', function(){
             var params = [];
             $.each($('.add-new-modules input:checked'), function(i, e){
                params.push($(e).val());
             });
             if (params.length > 0){
                order.addModule(params, $('.tab-pane.active'), setPlugin);
             }
             $('.pop-up-close').trigger('click');
             return false;
           })
           
           e.preventDefault();
        });
        
        $('.adjust_tax').click(function(){
            var prefix = $(this).data('prefix');
            bootbox.dialog({
						message: '<div class=""><label class="control-label">'+"{$smarty.const.TEXT_CONFIRM_RESET_TOTALS}"+'</label></div>',
						title: "{$smarty.const.ICON_WARNING}",
						  buttons: {
							cancel: {
							  label: "{$smarty.const.TEXT_BTN_NO}",
							  className: "btn-cancel btn-left",
							},
							success: {
                                label: "{$smarty.const.TEXT_BTN_YES}",
                                className: "btn-primary",
                                callback: function() {
                                    $.post('orders/order-edit?orders_id={$oID|default:null}',{
										'action' : 'adjust_tax',
                                        'currentCart': $('input[name=currentCart]').val(),
                                        'adjust_prefix':prefix,
									}, function(data, status){
										if (status == 'success'){
											$('#payment_holder').html(data.payment_details);
											$('#totals_holder').html(data.order_total_details);
											$('#totals_holder .mask-money').setMaskMoney();
											$('#message').html(data.message);	
											localStorage.orderChanged = true;
										}
									}, 'json' );
                                }
							},
						  }
					  });
        });

		$('.use-recalculation').keyup(function(){
            var control = $(this).data('control');
            var marker = $(this).data('marker');
            var _diff = eval(control).diff;
            if (_diff == '0') _diff = 1;
            if (marker == 'ex'){
                var value = $(this).val() *  _diff ;
                $('input[name="update_totals['+control+'][in]"]').val(value.toFixed(2) );
            } else {
                var value = $(this).val() / _diff;
                $('input[name="update_totals['+control+'][ex]"]').val(value.toFixed(2) );
            }
		});


        $('.order_totals').each(function () {
            const $scrollBox = $(this);
            const $scrollBar = $scrollBox.parent();
            $scrollBox.css('position', 'relative');
            $scrollBar.css('position', 'relative');
            let scrollTopOld = 0;
            const topGap = 170;

            boxPosition();
            $(window).scroll(boxPosition);

            function boxPosition(){
                let absTop, boxHeight, barHeight, windowHeight, scrollTop, fromTopBar,
                    fromBottomBar, fromTopWindow, fromBottomWindow, barTop;

                recalculateSizes();
                if (boxHeight+10 > barHeight || boxHeight + topGap > windowHeight) {
                    $scrollBox.css('top', 0);
                    return null;
                }
                if (fromTopWindow < topGap) {
                    $scrollBox.css('top', topGap - (barTop - scrollTop))
                }

                recalculateSizes();
                if (scrollTop) {
                    $scrollBox.css('top', scrollTop)
                } else {
                    $scrollBox.css('top', 0)
                }
                recalculateSizes();
                if (fromBottomBar < 0) {
                    $scrollBox.css('top', barHeight - boxHeight)
                }
                recalculateSizes();
                if (fromTopBar < 0) {
                    $scrollBox.css('top', barHeight - boxHeight)
                }

                function recalculateSizes() {
                    absTop = $scrollBox.offset().top;
                    boxHeight = $scrollBox.height();
                    barHeight = $scrollBar.height();
                    windowHeight = $(window).height();
                    scrollTop = $(window).scrollTop();
                    fromTopBar = $scrollBox.position().top;
                    fromBottomBar = barHeight - boxHeight - fromTopBar;
                    fromTopWindow = absTop - scrollTop;
                    fromBottomWindow = windowHeight - (absTop + boxHeight - scrollTop);
                    barTop = $scrollBar.offset().top;
                }
            }
        })

	})(jQuery);
</script>
</div>