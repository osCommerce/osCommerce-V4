{use class="frontend\design\Info"}
<div class="address_book">
<div class="buttonBox buttons topButtons">
	{if $addr_process != ''}
	<div class="button1 right-buttons"><a href="{$addr_process}" class="btn-2">{$smarty.const.IMAGE_BUTTON_ADD_ADDRESS}</a></div>
	{/if}	
</div>
<h1>{$smarty.const.HEADING_TITLE}</h1>
{if $message !=''}
 {$message}
{/if}

<div class="topInfo after">
	<div class="leftMes">
		<div>{$smarty.const.PRIMARY_ADDRESS_TITLE}</div>
		<ul><li>{$smarty.const.PRIMARY_ADDRESS_DESCRIPTION}</li></ul>
	</div>
	<div class="rightMes"><ul><li>{$max_val}</li></ul></div>
</div>
<div class="address_book_row after">
	{foreach $address_array as $address name=addr}
		{if $smarty.foreach.addr.index % 2 == 0 && $smarty.foreach.addr.index != 0}
		</div><div class="address_book_row after">
		{/if}
		<div class="column{if $address.address_book_id == $address.default_address} primary_bg{/if} js-addressBlock">
			<h2><strong>{$address.text}</strong><a class="btn-edit" href="{$address.link_edit}">{$smarty.const.SMALL_IMAGE_BUTTON_EDIT}</a><a class="btn-del" href="{$address.link_delete}">&nbsp;</a></h2>
			<div class="column_wrapper">
				<div class="default_address"><span>{$smarty.const.TEXT_PRIMARY}</span><input type="radio" name="is_default" value="{$address.address_book_id}" class="check-on-off"{if $address.address_book_id == $address.default_address} checked{/if}></div>
				<div class="default_data">{$address.customers}<br>{$address.format}</div>
			</div>
		</div>
	{/foreach}
	
</div>
<div class="buttonBox buttons">
	{if $addr_process != ''}
	<div class="button1 right-buttons"><a href="{$addr_process}" class="btn-2">{$smarty.const.IMAGE_BUTTON_ADD_ADDRESS}</a></div>
	{/if}
    <div class="button2 left-buttons"><a href="{$link_back}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
</div>

</div>
<script type="text/javascript">
function switchStatement(is_default, customers_id) {
    var postData = { 'is_default' : is_default, 'customers_id' : customers_id };
    postData[$('meta[name="csrf-param"]').attr('content')] = $('meta[name="csrf-token"]').attr('content');
    $.post("account/switch-primary", postData, function(data, status){
        if ( data.status && data.status==='ok' ) {
            var $chkCollection = $('input[name="is_default"]');
			$('.js-addressBlock').removeClass('primary_bg');
            $chkCollection.each(function(){
                this.checked = ( parseInt(this.value,10)==data.default_address_id );
                var $chk = $(this);
                if ( this.checked ) {
                    $chk.parents('.js-addressBlock').addClass('primary_bg');
                }
                if ( typeof $chk.bootstrapSwitch === 'function' ) {
                    $chk.bootstrapSwitch('state', this.checked, true);
                }
			});
        } else {
            alert("Request error.");
        }
    },"json");
}

tl('{Info::themeFile('/js/bootstrap-switch.js')}', function(){
  var customers_id = {$customer_id};

  {\frontend\design\Info::addBoxToCss('switch')}
  $(".check-on-off").bootstrapSwitch({
    offText: '{$smarty.const.TEXT_NO}',
    onText: '{$smarty.const.TEXT_YES}',
    onSwitchChange: function (element, arguments) {
      switchStatement(element.target.value, customers_id);
      return true;
    }
  });
})
</script>