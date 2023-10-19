{use class="yii\helpers\Html"}
<div class="wb-or-prod edit_product_popup">
    <div class="widget box box-no-shadow">
        <div class="popup-heading">{$smarty.const.TEXT_ADD_GIVEAWAY}</div>
        <div class="widget-content">				
			<div class="ga_wrapper">
			  <div class="ga_row after">
			  {foreach $products as $product name=foo}
				{if $smarty.foreach.foo.iteration % 2 == 1 && $smarty.foreach.foo.iteration != 1}
				</div><div class="ga_row after">
				{/if}
				<div class="ga_column after">
				  
				  <div class="ga_img">
					<img src="{$product.image}" alt="">
				  </div>
				  
				  <div class="ga_ovr">
					{if $product.active}
                    {Html::beginForm(\yii\helpers\Url::to($queryParams), 'post', ['class' => 'frmGaProduct'])}					
                        {Html::hiddenInput('action', 'add_giveaway')}
					{/if}
					<div class="ga_name">
						{$product.products_name}
					  {if count($product.attributes)>0}
					  <div class="ga_attributes">
						{foreach $product.attributes as $item}
						  <div>
							<select class="js_ga_select form-select" name="giveaways[{$product.ga_idx}][id][{$item.id}]" data-required="{$smarty.const.PLEASE_SELECT|escape:'html'} {$item.title|escape:'html'}" data-empty-option="{$smarty.const.SELECT|escape:'html'} {$item.title|escape:'html'}">
							  {foreach $item.options as $option}
								<option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
							  {/foreach}
							</select>
						  </div>
						{/foreach}
					   </div>
					   {/if}
					</div>
					<div class="ga_qty">{$product.price_b}</div>
					<div class="ga_price after">
					  {if $product.active }                        
						<input type="hidden" name="giveaways[{$product.ga_idx}][products_id]" value="{$product.products_id}">
						<span class="checkBoxWrap">
                            {Html::checkbox('giveaway_switch['|cat:$product.ga_idx|cat:']', $product.single_checkbox, ['value' => 1, 'onclick' => 'this.form.submit();'])}
                        </span>
					  {/if}
					  <br/>
					  {$product.giveaway_note}
					</div>
					{if $product.active }
					{Html::endForm()}
					{/if}
				  </div>
				</div>
			  {/foreach}
			  </div>
			</div>

		</div>
			<div class="noti-btn">
			  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
              <div><span class="btn btn-apply" >{$smarty.const.IMAGE_APPLY}</span></div>
			</div>		
	</div>
</div>

<script type="text/javascript">

    $('.js_ga_select').each(function(){
      var $select = $(this);
      if ( !$select.attr('data-empty-option') ) return;
      $select.prepend('<option value="0">'+$select.attr('data-empty-option')+'</option>')
    });

    $('.frmGaProduct').on('submit',function() {
      var form = this;
      var need_select_option = false,
              chck = $(this).find('input[name^="giveaway_switch"]');
      if ( chck.length>0 && chck[0].checked==false ) {
        return true;
      }
      $('.js_ga_select').each(function(){ if ($(this).val()=='0') need_select_option=true; });
      if ( need_select_option ) {
        if ( typeof alertMessage == 'function' ) {
          alertMessage('<div class="widget box"><div class="widget-content">{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}</div><div class="noti-btn"><div><span class="btn btn-cancel">{$smarty.const.TEXT_OK}</span></div></div></div>');
        }else{
          alert('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}');
        }

        if ( chck.length>0 ) {
          $(chck[0]).bootstrapSwitch('state',false/*state*/,true/*skipevent*/);
          chck[0].checked = false;
        }
        return false;
      }
      $.post($(form).attr('action'), $(form).serializeArray(), function(data){
        if (data.status == 'ok'){
            window.location.reload();
        } else if (data.hasOwnProperty('message')) {
            order.showMessage(data.message, true);                        
        }
      }, 'json');
      return false;
    });
    
    var last_form = $("input[name^='giveaway_switch']:checked").parents('form');
    
    $("input[name^='giveaway_switch']").on('click switchChange.bootstrapSwitch', function(element, arguments){ //
        var _this = this;
        $.each($("input[name^='giveaway_switch']"),  function(i,e){
            if (e != element.target && $(e).bootstrapSwitch('state')) {
                $(e).bootstrapSwitch('state', false);
            }
        });
        $(element.target).bootstrapSwitch('state', arguments);
        last_form = $(element.target).parents('form');          
    });
    
    
    $('.btn-apply').click(function(){
        if ($("input[name^='giveaway_switch']:checked").length>0){
            var $divna = $("input[name^='giveaway_switch']:checked").parents('form');            
            var event = jQuery.Event( "submit" );
            $divna.trigger(event);
            if ( event.result ) {
                $divna.submit();
            }
        } else {
            var $divna = last_form;
            $($divna).find('input[name=action]').val('remove_giveaway');
            var event = jQuery.Event( "submit" );
            $divna.trigger(event);
            if ( event.result ) {
                $divna.submit();
            }
        }
    });
    $("input[name^='giveaway_switch']").bootstrapSwitch(
        {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
        }
    );	
	

</script>