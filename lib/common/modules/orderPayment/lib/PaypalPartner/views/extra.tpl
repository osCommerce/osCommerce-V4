{use class="common\helpers\Html"}

<style>
    .div-installPPP { display:inline-block}
    form#saveModules.paypal_partner .not-applicable:after { content:'*'; font-size: 13px;}
    form#saveModules.paypal_partner .applicable { font-weight: bolder; font-size: 13px;}
    #ppp_not_applicabe_note { }
    #ppp_applicabe_note { padding:1em; font-size:13px; }
    .scroll_ppp_checkout { cursor: pointer;}
</style>
{if $smarty.get.ppp_next == 4 || $seller->fee_editable==1}
  {$seller_fee_editable = 1}
{/if}
<a name="extra"></a>

<div style="display:none" id="save_to_continue">{$smarty.const.TEXT_PAYPAL_PARTNER_SAVE_TO_CONTINUE}</div>
<div id="paypal_partner_container">
{if empty($boardViaLink) && !$ownAPIDetails && $smarty.get.ppp_next!=4}
    {if false && $cor}
    <div class="row">
      <div class="alert alert-danger">
        {$smarty.const.PAYPAL_PARTNER_SAME_DOMAIN} <a href="{$platform_base_url}">{$platform_base_url}</a>
      </div>
    </div>
  {/if}
  {if empty($seller->payer_id) && intval($smarty.get.ppp_next)>1}
    <div class="row">
      <div class="alert alert-info">
        {$smarty.const.PAYPAL_PARTNER_CHECK_DETAILS_AND_BOARDING}
      </div>
    </div>
  {/if}
{/if}

<div class="row" id="boardingtarget">
    <div class="extra-buttons-container">
{if $cancelUrl}
	<a href="{$cancelUrl}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
{/if}

{if empty($seller->own_client_id) && empty($seller->own_client_id) }
  <div id="installPPP" class="div-installPPP" ><a class="btn btn-primary btn-no-margin" href="">{$smarty.const.ADD_PAYPAL}</a></div>
{/if}

    {Html::a($smarty.const.MODULE_PAYMENT_PAYPAL_PARTNER_API_TEST, $checkLink, ['class' => 'btn btn-onboard check-credentials btn-primary dis_prod', 'disabled'=>1])}
    {Html::hiddenInput('checkVal', $checkVal)}
    {Html::hiddenInput('psi_id', $seller->psi_id)}

    {if $ownAPIDetails && !empty($seller->payer_id) && $seller->status==1 && ($smarty.get.ppp_next!=4)}
        {*if !$boardingActive || !(empty($seller->payer_id) && ($smarty.get.ppp_next!=4))*}
        {Html::a($smarty.const.PAYPAL_PARTNER_DELETE_SELLER, $unBoardLink, ['class' => 'btn btn-onboard btn-primary onboard-delete', 'style'=>'float:right'])}
    {/if}
  </div>
</div>

{if !$ownAPIDetails}
    <br/>
<div id="extraHeading" class="alert {if $seller->is_onboard || $smarty.get.setup==1}alert-info{else}alert-danger{/if}">
    {if $smarty.get.setup==1}
        {$smarty.const.PAYPAL_PARTNER_SELLER_SETUP}
    {else}
        {if $seller->is_onboard}
          {$smarty.const.PAYPAL_PARTNER_SELLER_BOARDED}
        {else}
          {$smarty.const.PAYPAL_PARTNER_SELLER_NOT_BOARDED}
        {/if}
    {/if}
</div>
{/if}

{include "./info.tpl"}
    <div class="col-md-12 after" {if !$seller->isNewRecord && !$seller->isOnBoarded() && !$ownAPIDetails}style="display:none"{/if}>
    <div class="col-md-4">
      {if !$ownAPIDetails}
                {if empty($boardViaLink)}
        <div class="col-2">
          <label>{$smarty.const.PAYPAL_PARTNER_SELLER_TRACKING_ID}</label>
          {$params = ['class' => 'form-control', 'style' => 'width:100%', 'id' => 'txtTrackingId']}
          {if !$seller->isNewRecord}{$params['readonly'] = true}{/if}
              {Html::activeTextInput($seller, 'tracking_id', $params)}
          </div>
                {/if}
      {else}
        <div class="col-2">
          <label>{$smarty.const.PAYPAL_PARTNER_OWN_CLIENT_ID}</label>
          <div class="input-group">
              {Html::activeTextInput($seller, 'own_client_id', ['class' => 'form-control'])}
          </div>
        </div>
        <div class="col-2">
          <label>{$smarty.const.PAYPAL_PARTNER_OWN_CLIENT_SECRET}</label>
          <div class="input-group">
              {Html::activeTextInput($seller, 'own_client_secret', ['class' => 'form-control'])}
          </div>
        </div>
      {/if}

        <div class="col-2">
          <label>{$smarty.const.PAYPAL_PARTNER_SELLER_MERCHANT_ID}</label>
          {$params = ['class' => 'form-control']}
          {if !$seller_fee_editable}
            {$params['readonly'] = true}
          {/if}
          {Html::activeTextInput($seller, 'payer_id', $params)}
        </div>

        <div class="col-2">
            <label>
                <span>{$smarty.const.PAYPAL_PARTNER_SELLER_EMAIL}</span>
                    {if !empty($smarty.get.merchantId) && $smarty.get.merchantId != $seller->email_address}
                        {Html::input('text', $seller->formName()|cat:'[email_address]', $smarty.get.merchantId)}
                    {else}
                {Html::activeTextInput($seller, 'email_address', ['class' => 'form-control'])}
                    {/if}
            </label>
                {if !empty($smarty.get.merchantId) && $smarty.get.merchantId != $seller->email_address}
                    <span class="small save-note">{$smarty.const.PAYPAL_PARTNER_SELLER_EMAIL_SAVE_NOTE}</span>
                {/if}
        </div>
            {if empty($boardViaLink)}
        <div class="col-2">
            <label>
                <span>{field_label const="ENTRY_FIRST_NAME" configuration=$address->get('FIRSTNAME')}</span>
                {Html::activeTextInput($address, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
            </label>
        </div>
        <div class="col-2">
            <label>
                <span>{field_label const="ENTRY_LAST_NAME" configuration=$address->get('LASTNAME')}</span>
                {Html::activeTextInput($address, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
            </label>
        </div>
        <div class="col-2">
            <label>
                <span>{field_label const="ENTRY_TELEPHONE_NUMBER"}</span>
                {Html::activeTextInput($address, 'telephone', ['class' => 'form-control'])}
            </label>
        </div>
        <div class="col-2">
            <label>
                <span>{field_label const="PAYPAL_PARTNER_FEE"}</span>
                {$params['class'] = 'form-control'}
                {if !$seller_fee_editable}
                  {$params['readonly'] = true}
                {/if}
                {Html::activeTextInput($seller, 'fee_percent', $params)}
            </label>
        </div>
            {/if}
{if !$ccpActive}{* ccp settings in second column*}
    </div>

    <div class="col-md-3">
{/if}
            {if empty($boardViaLink)}
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_COUNTRY" configuration=$address->get('COUNTRY')}</span>
                    {Html::activeDropDownList($address, 'country', $address->getAllowedCountries(), ['data-required' => "{$smarty.const.ENTRY_COUNTRY_ERROR}", 'class' => 'form-control', 'data-iso' => $address->getAllowedCountriesISO(), 'id'=> 'selectCountry'])}
                </label>
            </div>
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_STREET_ADDRESS" configuration=$address->get('STREET_ADDRESS')}</span>
                    {Html::activeTextInput($address, 'street_address', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STREET_ADDRESS_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH)}", 'class' => 'form-control'])}
                </label>
            </div>
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_SUBURB" configuration=$address->get('SUBURB')}</span>
                    {Html::activeTextInput($address, 'suburb', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_SUBURB_ERROR}", 'class' => 'form-control'])}
                </label>
            </div>
            <div class="col-3 address-wrap city-wrap">
                <label>
                    <span>{field_label const="ENTRY_CITY" configuration=$address->get('CITY')}</span>
                    {Html::activeTextInput($address, 'city', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_CITY_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_CITY_ERROR, ENTRY_CITY_MIN_LENGTH)}", 'class' => 'form-control'])}
                </label>
            </div>
            <div class="col-3 address-wrap state-wrap">
                <label>
                    <span>{field_label const="ENTRY_STATE" configuration=$address->get('STATE')}</span>
                    {Html::activeTextInput($address, 'state', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_STATE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH)}", 'class' => 'form-control', 'id' => "selectState"])}
                </label>
            </div>
            <div class="col-3">
                <label>
                    <span>{field_label const="ENTRY_POST_CODE" configuration=$address->get('POSTCODE')}</span>
                    {Html::activeTextInput($address, 'postcode', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH)}", 'class' => 'form-control'])}
                </label>
            </div>
            <div class="col-2">
                <label>
                    <span>{field_label const="ENTRY_COMPANY" configuration=$address->get('COMPANY')}*</span>
                    {Html::activeTextInput($address, 'company', ['data-pattern' => "{$re1}1{$re2}", 'data-required' => "{$smarty.const.ENTRY_COMPANY_ERROR}", 'class' => 'form-control'])}
                </label>
            </div>
        <div class="col-full">
            <span class="required">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
        </div>
    </div>

            {/if}

            {if is_array($sellerBoardingDetails) && !empty($sellerBoardingDetails)}
</div>
</div>

<div id="ppp_advanced" class="ppp-advanced-link collapsed">
    <a href="javascript:void(0)" onclick="$('#accountDetails').toggle();$('#ppp_advanced').toggleClass('collapsed'); return false;" class="switch-collapse">{$smarty.const.PAYPAL_PARTNER_TEXT_ADVANCED_SETTINGS|escape}</a></div>
    <div class="" id="accountDetails" style="display:none">

    <div class="">
        <div class="widget box box-no-shadow account-details" style="width:49%; margin:0 5px;float: left;">
            <div class="col-full widget-header"><h4>{$smarty.const.PAYPAL_PARTNER_TEXT_ACCOUNT_DETAILS}</h4></div>
            {foreach $sellerBoardingDetails as $sbdGroup => $sbdData}
                {*<div class="col-full"><span class="h4">{$sbdGroup}</span></div>*}
                {if is_array($sbdData)}
                <div class="widget-content">
                    {foreach $sbdData as $name => $value}
                    <div class="col-full {if $sbdGroup=='errors'}alert alert-danger{else}{if $sbdGroup=='warnings'}alert alert-warning{else} divider{/if}{/if} after">
                        {if !is_numeric($name)}
                        <div class="col-md-6">
                            <label class="title -pull-right">{$name}</label>
                        </div>
                        {/if}
                        <div class="{if !is_numeric($name)}col-md-6{else}col-md-12{/if}">
                            <span class="value modules-description">{$value}</span>
                        </div>
                    </div>
                    {/foreach}
                </div>
                {/if}
            {/foreach}


{if $ccpActive}{* advanced credit and debit cards (ACDC) settings*}
        <div class="col-full widget-header"><h5>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_FIELDS}</h5></div>
        <div class="widget-content">
        <div class="col-2 divider">
            <label>
                <span>{$smarty.const.ENTRY_STATUS}</span>
                {Html::checkbox('paypal_partner_ccp_status', $seller.paypal_partner_ccp_status, ['value' => 1, 'class'=>'check_on', 'onchange'=> '$("#ccp_block").toggle();'])}
            </label>
        </div>
        <div id='ccp_block' class="col-full" {if empty($seller->paypal_partner_ccp_status)}style="display:none{/if}">
            <div class="col-2 divider">
                <h4>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DS}</h4>
                <div class="col-full modules-description">{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DS_DESCRIPTION}<br /></div>
                <label>
                    <span>{$smarty.const.ENTRY_STATUS}</span>
                    {Html::checkbox('paypal_partner_3ds_status', !isset($seller3DS['status']) || $seller3DS['status'], ['value' => 1, 'data-default' => $seller3DS['defaults']['status']])}
                </label>
            </div>
            <div class="col-2 divider">
                <div class="">
                    <h6>{$smarty.const.TEXT_PAYPAL_PARTNER_CONTINGENCIES}<span class="colon">:</span></h6>
                </div>
                <div class="">
                    <label>
                        {Html::radio('paypal_partner_3ds_contingencies',(!isset($seller3DS['contingencies']) || ( $seller3DS['contingencies'] == 'SCA_WHEN_REQUIRED')), ['value' => 'SCA_WHEN_REQUIRED'])}
                        <span>SCA_WHEN_REQUIRED</span>
                    </label>
                    <label>
                        {Html::radio('paypal_partner_3ds_contingencies', isset($seller3DS['contingencies']) && $seller3DS['contingencies'] == 'SCA_ALWAYS', ['value' => 'SCA_ALWAYS'])}
                        <span>SCA_ALWAYS</span>
                    </label><br />
                </div>
            </div>
{** }
            <div class="col-2 divider">
                <h4>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DSA}</h4>
                <div class="col-full modules-description">{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DSA_DESCRIPTION}</div>
                <div class="col-md-12 block-3dsa">
                    <table >
                        <tr>
                            <td><label>{$smarty.const.TEXT_PAYPAL_PARTNER_3DSA_ENROLLMENTSTATUS}</label></td>
                            <td><label>{$smarty.const.TEXT_PAYPAL_PARTNER_3DSA_AUTHENTICATION_STATUS}</label></td>
                            <td><label>{$smarty.const.TEXT_PAYPAL_PARTNER_3DSA_LIABILITYSHIFT}</label></td>
                            <td><label>{$smarty.const.TEXT_PAYPAL_PARTNER_3DSA_ACCEPT}</label></td>
                        </tr>
                        {foreach $seller3DS['3dsa'] as $threeD}
                        <tr>
                            <td><label title="{$threeD['e_desc']|escape}">{$threeD['e']}</label></td>
                            <td><label title="{$threeD['a_desc']|escape}">{$threeD['a']}</label></td>
                            <td><label title="{$threeD['l_desc']|escape}">{$threeD['l']}</label></td>
                            <td class="alert {if $threeD['def_state_class']=='def-checked'}alert-success{else}alert-danger{/if}">
                                <label>{Html::checkbox('paypal_partner_3ds_'|cat:$threeD['key'], $threeD['checked'], ['value' => '1', 'class' => 'c-3dsa '|cat:$threeD['def_state_class']])}</label>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </div><br />
            </div>
            <div class="col-2 text-right">
                <button class="btn" onclick="return set3DSDefault()">{$smarty.const.IMAGE_RESET_TO_DEFAULT}</button>
            </div>
{**}
        </div>
        </div>
{/if}

        </div>


        <div class="widget box box-no-shadow account-settings" style="width:49%; margin:0 5px;float: left;">
            {*<div class="col-full widget-header"><h4>{$smarty.const.PAYPAL_PARTNER_TEXT_SETTINGS}</h4></div>*}
{if !empty($webhooksRequiredList)}{* webhooks *}
            {*<div class="col-full">&nbsp;</div>*}
            <div class="col-full widget-header"><h4>{$smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS}</h4></div>
            <div class="col-full">
                <div class="col-md-6">
                    <div class="widget-content">
                        <div class="col-md-12 widget">{$smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBED}</div>
                        {if is_array($webhooksList)}
                        {foreach $webhooksList as $webhook}
                            <div class="col-md-12 small">{$webhook}</div>
                        {/foreach}
                        {/if}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="widget-content">
                        <div class="col-md-12 widget">{$smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS_REQUIRED}<small class="note asterisk">*</small>
                        {if $missedWh}
                            {Html::a($smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBE, $whSubscribeLink, ['class' => 'btn btn-onboard btn-primary onboard-subscribe', 'style'=>'float:right'])}
                        {/if}
                        </div>

                        <div class="col-md-12 alert{if $missedWh} alert-danger{else} alert-success{/if}">
                        {foreach $webhooksRequiredList as $webhook}
                            <div class="col-md-12 small">{$webhook}</div>
                        {/foreach}
                        </div>
                        <small class="note">{$smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS_REQUIRED_NOTE}</small>
                    </div>
                </div>
            </div>
{/if}

    </div>
            {/if}
</div>
</div>
    <script>
    /*
        var clipboard = new ClipboardJS('.js-clipboard-copy');
        clipboard.on('success', function(e) {
            e.clearSelection();
            var $tooltipTarget = $(e.trigger);
            $tooltipTarget.tooltip('show');
            setTimeout(function(){
                $tooltipTarget.tooltip('hide');
            },2000);
        });
        $('.js-clipboard-copy').tooltip({
            title:'{$smarty.const.TEXT_COPIED_TO_CLIPBOARD|escape:'javascript'}',
            placement:'left',
            trigger:'manual'
        });
     */
    $('.btn-onboard').on('click', function () {
            var url = this.href;
            var el = this;
            var confirmed = false;
            var postData = { };

            if ($(this).hasClass('check-credentials')) {
                postData = $('#saveModules').serialize();
            }

            if ($(this).hasClass('onboard-delete')) {
                bootbox.confirm({
                    title: '{$smarty.const.IMAGE_CONFIRM|escape:javascript}',
                    message: '{$smarty.const.TEXT_PAYPAL_PARTNER_UNLINK_PROMPT|escape:javascript}',
                    buttons: {
                        confirm: {
                            label: '{$smarty.const.TEXT_YES|escape:javascript}',
                            className: 'btn-danger'
                        },
                        cancel: {
                            label: '{$smarty.const.TEXT_NO|escape:javascript}',
                            className: 'btn-success'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            $.post(url, { }, function (data) {
                                if (data.reload == 1) {
                                    var search = '';
                                    if (data.params) {
                                        search = data.params;
                                    } else {
                                        try {
                                            search = location.search
                                                .replace(/[\?&]ppp_next=[^&]+/, '')
                                                .replace(/[\?&]action=[^&]+/, '')
                                                .replace(/^&/, '');
                                        } catch (e) {
                                        }
                                    }
                                    try {
                                        history.replaceState &&
                                        history.replaceState(null, '', location.pathname + '?' + search);
                                    } catch (e ) {
                                        window.location.href = location.pathname + '?' + search + '#extra';
                                        return;
                                    }
                                    window.location.reload();
                                } else if (data.preload == 1) {
                                    $('#'+data.block).html(data.html);
                                } else {
                                    $(el).parent().append('<div id="boardingPopup">' + '</div>');
                                    $('#boardingPopup').popUp({ 'event': 'show'});
                                    $('.pop-up-content').html(data);
                                }
                            });
                        }
                    }
                });
            } else
            if ($(this).hasClass('onboard-subscribe')) {
                bootbox.confirm('{$smarty.const.TEXT_PAYPAL_PARTNER_WEBHOOKS_SUBSCRIBE_CONFIRM|escape:javascript}', function (result) {
                    if (result) {
                        $.post(url, { }, function (data) {
                            if (data.reload == 1) {
                               /* var search = '';
                                if (data.params) {
                                    search = data.params;
                                } else {
                                    try {
                                        search = location.search
                                            .replace(/[\?&]ppp_next=[^&]+/, '')
                                            .replace(/[\?&]action=[^&]+/, '')
                                            .replace(/^&/, '');
                                    } catch (e) {
                                    }
                                }
                                history.replaceState && history.replaceState(
                                    null, '', location.pathname + '?' + search);*/
                                window.location.reload();
                            } else {
                                $(el).parent().append('<div id="boardingPopup">' + '</div>');
                                $('#boardingPopup').popUp({ 'event': 'show'});
                                $('.pop-up-content').html(data);

                            }
                        });
                    }
                });
            } else {
                confirmed = true;
            }
            if (confirmed) {
                $.post(url, postData, function (data) {
                    if (data.reload == 1) {
                        history.replaceState && history.replaceState(
                            null, '', location.pathname + location.search.replace(/[\?&]ppp_next=[^&]+/, '').replace(/^&/, '?')
                          );
                        window.location.reload();
                    } else {
                        $(el).parent().append('<div id="boardingPopup">' + '</div>');
                        $('#boardingPopup').popUp({ 'event': 'show'});
                        $('.pop-up-content').html('<div class="alert">' + data.html + '</div>');
                    }
                });
            }
          return false;
    });

    {if $gologin }
          $('.btn-login').trigger('click');
          //$('#extraHeading').srollTo();
    {else}
        {if $smarty.get.ppp_next==2 || $smarty.get.ppp_next==3 }
            $('.btn-onboard').trigger('click');
        {/if}
    {/if}

        $('#selectState').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "{$app->urlManager->createUrl('customers/states')}",
                    dataType: "json",
                    data: {
                    term: request.term,
                    country: $("#selectCountry").val()
                    },
                success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 0,
            autoFocus: true,
            delay: 0,
            appendTo: '.state-wrap',
            open: function (e, ui) {
              if ($(this).val().length > 0) {
                var acData = $(this).data('ui-autocomplete');
                acData.menu.element.find('a').each(function () {
                  var me = $(this);
                  var keywords = acData.term.split(' ').join('|');
                  me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
              }
            },
            select: function (event, ui) {
                $('input[name="city"]').prop('disabled', true);
                if (ui.item.value != null) {
                    $('input[name="city"]').prop('disabled', false);
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
        });

    function set3DSDefault() {
        {* 2do print_r($seller3DS['defaults''], 1) *}
        var key = 'status';
        if ($('input[name="paypal_partner_3ds_' + key + '"]').length > 0 && $('input[name="paypal_partner_3ds_' + key + '"]:checked').length == 0) {
            $('input[name="paypal_partner_3ds_' + key + '"]').click();
        }
        var val = 'SCA_WHEN_REQUIRED';
        key = 'contingencies';
        if ($('input[name="paypal_partner_3ds_' + key + '"][value="' + val + '"]').length > 0 && $('input[name="paypal_partner_3ds_' + key + '"][value="' + val + '"]:checked').length == 0) {
            $('input[name="paypal_partner_3ds_' + key + '"][value="' + val + '"]').click();
        }

        $('input.c-3dsa.def-unchecked:checked, input.c-3dsa.def-checked:not(:checked)').click();

        return false;
    }

var ppp_mode = '';
$(document).ready(function() {
    try {
        ppp_mode = $('input[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER\]"]:checked').val();
        {if $ppp_mode}
        if (ppp_mode != '{$ppp_mode}') {
            $('input[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER\]"]:not(:checked)').click();
            ppp_mode = $('input[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER\]"]:checked').val();
        }
        {/if}
    } catch (e ) {
    }
    $('input[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER\]"]').on('click', function() {
        if (ppp_mode != '') {
            if ($(this).val() != ppp_mode && ppp_mode != '') {
                $("#save_to_continue").show();
                $(".div-installPPP").hide();
                $("#paypal_partner_container").hide();
            } else {
                $("#save_to_continue").hide();
                $("#paypal_partner_container").show();
                $(".div-installPPP").show();
            }
        }
    });
//display mode at checkout
    $('select[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT\]"]')
    .on('click', function() {
        if ($(this).val() == 'Fields' ) {
            $('#accountDetails').show();
            $('#ppp_advanced').removeClass('collapsed');
            $(".paypal-partner-pm.apm").parents('label').show();
        } else {
            $(".paypal-partner-pm.apm").parents('label').hide();
        }
        checkApplicable();
    })
    .click();

    $('#check_connect_btn').on('click', function() {
        $.post(url, { }, function (data) {
            $(el).parent().append('<div id="boardingPopup">' + '</div>');
            $('#boardingPopup').popUp({ 'event': 'show'});
            $('.pop-up-content').html(data);
        })
    });

/// test API button
    $("#sellerinfo-own_client_id, #sellerinfo-own_client_secret, #sellerinfo-payer_id").on('change', allowCheckAPI);
    allowCheckAPI();

/// APMs checkers
    var apmSelector = 'input[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING\]\[\]"][type="checkbox"]';
    $(apmSelector).on('click', function() {
        if ($(this).val() == 'all') {
            var master_checked = this.checked;
            $(apmSelector).not('[value="all"]').prop('checked', master_checked);
        } else {
            //var checked = $(apmSelector+':checked').not('[value="all"]').length;
            var unchecked = $(apmSelector+':not(:checked)').not('[value="all"]').length;
            if (unchecked == 0) {
                $(apmSelector+'[value="all"]').prop('checked', true);
            } else {
                $(apmSelector+'[value="all"]').prop('checked', false);
            }
        }
        if ( typeof $.uniform !=='undefined' ) $.uniform.update();
    });


}); //document ready

/**
* update test API button status
*/
function allowCheckAPI() {
    if ($("#sellerinfo-own_client_id").val() != '' && $("#sellerinfo-own_client_secret").val() != '' && $("#sellerinfo-payer_id").val() != '' ) {
        $('.check-credentials').removeClass('dis_prod');
        $('.check-credentials').attr('disabled', false);
    } else {
        $('.check-credentials').addClass('dis_prod');
        $('.check-credentials').attr('disabled', true);
    }
}

/**
* show/hide "not applicable APM" message according platform country
*/
function checkApplicable() {

    if ($(".paypal-partner-pm.apm.not-applicable:visible").length>0 && $("#ppp_not_applicabe_note").length==0) {
        $(".paypal-partner-pm:last").parents('label').parent().append('<div id="ppp_not_applicabe_note" class="alert-info">{if !empty($smarty.const.MODULE_PAYMENT_PAYPAL_PARTNER_NOT_APPLICABLE_COUNTRY)}{$smarty.const.MODULE_PAYMENT_PAYPAL_PARTNER_NOT_APPLICABLE_COUNTRY|escape:javascript}{else}* not applicable in selected frontend country{/if}</div>');
    } else {
        $("#ppp_not_applicabe_note").remove();
    }

    if ($(".paypal-partner-pm.apm.applicable").length>0 && $(".paypal-partner-pm.apm.applicable:visible").length == 0 && $("#ppp_applicabe_note").length==0) {
        $(".paypal-partner-pm:last").parents('label').parent().append('<div id="ppp_applicabe_note" class="alert-warning scroll_ppp_checkout" onclick="scroll_ppp_checkout();">{$smarty.const.MODULE_PAYMENT_PAYPAL_PARTNER_APMS_SHOW_FIELDS|escape:javascript}</div>');
    } else {
        $("#ppp_applicabe_note").remove();
    }
}


function scroll_ppp_checkout() {
    //$('select[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT\]"]').get(0).scrollIntoView({ behavior: 'smooth'});
    try {
        adj = parseInt($('.top_bead').height());
        adj1 = parseInt($('.navbar-fixed-top').height());
    } catch (e ) { }
    if (adj>0) {    } else { adj = 0 }
    if (adj1>0) {    } else { adj1 = 0 }
    $([document.documentElement, document.body]).animate({
        scrollTop: $('select[name="configuration\[MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT\]"]').offset().top-adj-adj1-10
    }, 500);
}
modePPP = 'edit';
    </script>