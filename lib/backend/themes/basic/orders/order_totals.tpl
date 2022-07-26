{use class="Yii"}
<div class="order_totals">
	{$inputs}
    <div style="display:none;" id="st">
     <div style="padding:5px;">
      <label>{$smarty.const.TEXT_PAID_AMOUNT} ({$currency})</label>  
     {tep_draw_input_field('paid_amount', '0', 'class="form-control mask-money"')}
     <label>{$smarty.const.TABLE_HEADING_COMMENTS}</label>
     <textarea name="comment" class="form-control"></textarea>
     <input type="hidden" name="action" value="update_amount">
     </div>
    </div>
</div>
<script>

        function savePaid(){
                $.post('orders/order-edit?orders_id={$oID}',
                    $('.form-paid').serialize()+'&currentCart='+$('input[name=currentCart]').val(),
                    function(data, status){
                        $('#totals_holder').html(data.order_total_details);
						$('#totals_holder .mask-money').setMaskMoney();
						$('#message').html(data.message);	                    
                        closePopup();
                    },'json');
                return false;
        
        }
        
	(function($){
		$('.totals_reset').click(function(e){
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
                                    $.post('orders/order-edit?orders_id={$oID}',{
										'reset_totals' : 'on',
                                        'currentCart':$('input[name=currentCart]').val(),
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


        $('.update-paid-amount').click(function(e){
            e.preventDefault();
            var paid = this;
            $(paid).popUp({
              data: $('#st').html(),
              event: 'show',
              only_show: true,
              box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading add-total-element'>{$smarty.const.TEXT_UPDATE_PAID_AMOUNT}</div><div class='pop-up-close'></div><form action='' class='form-paid' method='post' onSubmit='return savePaid();'><div class='pop-up-content'></div><div class='noti-btn'><div><button class='btn btn-cancel'>{$smarty.const.IMAGE_CANCEL}</button></div><div><input type='submit' class='btn btn-confirm save-amount' value='{$smarty.const.IMAGE_SAVE}'></div></div></form></div></div>",
           });            
        });
        
            
        $('.totals.edit-pt').click(function(){
            if ($(this).hasClass('btn')){
                $(this).removeClass('btn');
                if (typeof unformatMaskMoney == 'function') {
                    unformatMaskMoney();
                }                
                var element = $(this).parents('tr').find('input.use-recalculation:first');
                var control = $(element).data('control');
                addModule(control);
				localStorage.orderChanged = true;
            } else {
                $(this).addClass('btn');
                var elements = $(this).parents('tr').find('input.use-recalculation');
                $.each(elements, function(i,e){
                    $(e).attr('type','input');
                    if ($(e).data('control') != '$ot_bonus_points'){
                        $(e).setMaskMoney();
                    }
                    $(e).next().hide();
                });
            }
            //
        });
               
        $('.add-more').click(function(e){
            var event = window.event||e;
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
             addModule(params, true);
             $('.pop-up-close').trigger('click');
             return false;
           })
           
           event.preventDefault();
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
                                    $.post('orders/order-edit?orders_id={$oID}',{
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
					$('input[name=\"update_totals['+control.substr(1)+'][in]\"]').val(value.toFixed(2) );
				} else {
					var value = $(this).val() / _diff;
					$('input[name=\"update_totals['+control.substr(1)+'][ex]\"]').val(value.toFixed(2) );
				}
				$(this).focusout(function(){
					//addModule(control);
					//localStorage.orderChanged = true;
				})
				
		})            
            
	})(jQuery);        
</script>