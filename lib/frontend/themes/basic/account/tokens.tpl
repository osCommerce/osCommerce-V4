{use class="frontend\design\Info"}
{use class="common\helpers\Html"}
<div class="account-tokens" id="accountTokens">
<h1>{$smarty.const.HEADING_TITLE_TOKENS}</h1>
<div class="messages" id="accountTokensMessages">
{if $message !=''}
 {$message}
{/if}
</div>
<div id="accountNoTokens" {if $gateways_array|count}style="display: none"{/if}><h4>{$smarty.const.ACCOUNT_NO_TOKENS}</h4></div>
{foreach $gateways_array as $gatewayCode => $gateway}
  <div class="address_book_row after payment-block" id="payment-{$gatewayCode}">
  <h2>{$gateway.title}</h2>
  <div class="address_book_row after">
	{foreach $gateway.tokens as $token name=tkn}
		{if $smarty.foreach.tkn.index % 2 == 0 && $smarty.foreach.tkn.index != 0}
		</div><div class="address_book_row after">
		{/if}
		<div class="column js-token" id="token-{$token.id}">
      {Html::beginForm($token.link_edit, 'post', ['onsubmit' => 'return saveToken(this)', 'id'=> 'token-form-'|cat:$token.id, 'style' => 'display:none;'])}
      {Html::hiddenInput('token_name['|cat:$token.id|cat:']', $token.card_name)}
      {if $token.card_name!=''}<span class="cc-smaller">{/if}
      <i class="cc-icon cc-{$token.card_type}"></i><span class="token-cc" id="token-cc-span-{$token.id}">{$token['last_digits']}</span>
      {if $token.card_name!=''}</span>{/if}
      <label for="{'isDefault_'|cat:$token.id|cat:'_'}" accesskey="d">
              {Html::checkbox('is_default['|cat:$token.id|cat:']', ($token.is_default==1), ['class' => 'token_default-'|cat:$token.payment_class])}{$smarty.const.TEXT_DEFAULT}
      </label>

      <div class="buttons-inline buttons edit-buttons" id="div-buttons-{$token.id}">
        <div class="button2 left-buttons"><a class="btn" data-token="{$token.id}" href="#" onclick="return cancelToken(this)">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
        <div class="button2 right-buttons"><button class="btn" data-token="{$token.id}" data-payment="{$token.payment_class}">{$smarty.const.TEXT_SAVE}</button></div>
      </div>
      {Html::endForm()}

      <div class="token-details">
        <h4>
          <strong class="token-name" id="token-name-span-{$token.id}">{$token.card_name}</strong> 
        </h4>
        {if $token.card_name!=''}<span class="cc-smaller">{/if}
        <i class="cc-icon cc-{$token.card_type}"></i><span class="token-cc" id="token-cc-span-{$token.id}">{$token['last_digits']}</span>
        {if $token.card_name!=''}</span>{/if}
      </div>
      {$eDate = explode('-', $token['exp_date'])}
      <div class="expiryDate">{$eDate[1]}/{$eDate[0]} </div>
      <div class="token-default token-default-{$token.payment_class} {if !$token.is_default}not-selected{/if}" id="token-default-{$token.id}">
        <i class="glyphicon glyphicon-ok" {if !$token.is_default}style="display:none"{/if}>{$smarty.const.TEXT_DEFAULT}</i>
      </div>
      <div class="token-actions" id="token-actions-{$token.id}">
        <a class="btn-edit link-action-ajax" data-token="{$token.id}" href="#" onclick="return editToken(this);">{$smarty.const.SMALL_IMAGE_RENAME}</a><a class="btn-del  link-action-ajax" href="{$token.link_delete}" data-token="{$token.id}" onclick="return deleteToken(this);">&nbsp;</a>
      </div>
		</div>

	{/foreach}
  </div>
  </div>
{/foreach}
</div>
<script type="text/javascript">
function cleanUpTokens() {
  $('#accountTokens .payment-block').each(function( i, el ){
      var gw = $(this);
      if ($('.js-token', gw).length==0) {
        gw.remove();
      }
    });
  if($('#accountTokens .payment-block').length==0) {
    $('#accountNoTokens').show();
  } else {
    $('#accountNoTokens').hide();
  }
}
function editToken(el) {
  var tId = $(el).attr('data-token');
  $('#token-form-'+tId).show();
  $('#token-name-span-'+tId).hide();
  $('#token-actions-'+tId).hide();
  $('#tokenName_'+tId+'_').attr('type', 'text');
  return false;
}
function cancelToken(el) {
  var tId = $(el).attr('data-token');
  $('#token-form-'+tId).hide();
  $('#token-name-span-'+tId).show();
  $('#token-actions-'+tId).show();
  $('#tokenName_'+tId+'_').attr('type', 'hidden');
  return false;
}
function deleteToken(el) {
    var _this = $(el);
    var id = _this.attr('data-token');
    confirmMessage('{$smarty.const.CONFIRM_DELETE_TOKEN|escape:'html'}' +"\n\n" + $("#token-cc-span-"+id).text(), function(){
        $.get(_this.attr('href'), function(data){
            var messages = '';
            $.each(data.messages, function(key, val){
                messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
                if (val['type'] == 'success'){
                    $("#token-"+id).remove();
                    cleanUpTokens();
                }
            });
            $('#accountTokensMessages').html(messages);
        }, 'json')
    }, '{$smarty.const.IMAGE_BUTTON_DELETE|escape}', '{$smarty.const.CANCEL|escape}');
    return false;
}
function saveToken(el) {
    var success = false;
    $.post(el.action, $(el).serialize(), function(data){
        var messages = '';
        $.each(data.messages, function(key, val){
            messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
            if (val['type'] == 'success'){
              success = true;
            }
        });
        if (success) {
          var tId = data.id;
          var name = data.name;
          $('#token-form-'+tId).hide();
          $('#token-name-span-'+tId).text(name).show();
          $('#token-actions-'+tId).show();
          $('#tokenName_'+tId+'_').attr('type', 'hidden').val(name);
          if (data.is_default) {
            $('.token-default-' + data.payment_class + ' i').hide();
            $('#token-default-' + tId + ' i').show();
            $('input.token_default-' + data.payment_class).prop('checked', false);
            $('#isDefault_' + tId + '_').prop('checked', true);
          }
        }
        $('#accountTokensMessages').html(messages);
    }, 'json');

    return false;
}
{*
tl('', function(){
})
*}
</script>