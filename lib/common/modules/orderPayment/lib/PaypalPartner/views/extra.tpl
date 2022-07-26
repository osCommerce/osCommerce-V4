{use class="common\helpers\Html"}
{if $smarty.get.ppp_next == 4 || $seller->fee_editable==1}
  {$seller_fee_editable = 1}
{/if}
<a name="extra"></a>

{if !$ownAPIDetails && $smarty.get.ppp_next!=4}
  {if $cor}
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

<div class="row">
  <div class="">
  {if !$ownAPIDetails }
    {if $boardingActive}
        {if empty($seller->payer_id) && ($smarty.get.ppp_next==1)}
          {Html::a($smarty.const.PAYPAL_PARTNER_LOGIN, $loginLink, ['class' => 'btn btn-login btn-onboard btn-primary'])}
        {/if}
        {if $seller->isNewRecord}
          {$smarty.const.PAYPAL_PARTNER_SAVE_TO_CREATE_ACCOUNT}
        {/if}

        {if empty($seller->payer_id) && ($smarty.get.ppp_next!=1)}
          {Html::a($smarty.const.PAYPAL_PARTNER_LOGIN, $loginLink, ['class' => 'btn btn-login btn-onboard btn-primary'])}
        {/if}

        {if !$seller->isNewRecord && !$seller->isOnBoarded()}
            {Html::a($smarty.const.PAYPAL_PARTNER_ONBOARD, $boardLink, ['class' => 'btn btn-onboard btn-primary'])}
        {/if}

        {if !$seller->isNewRecord && !empty($seller->payer_id) } {*$seller->isOnBoarded()*}
            {Html::a($smarty.const.PAYPAL_PARTNER_CHECK_ONBOARD, $checkBoardLink, ['class' => 'btn btn-onboard btn-primary'])}
        {/if}
    {/if}

    {if empty($seller->payer_id) && ($smarty.get.ppp_next!=4)}
      {Html::a($smarty.const.PAYPAL_PARTNER_OWN_API, $ownAPILink, ['class' => 'btn btn-primary'])}
    {/if}
  {else}
      {if empty($seller->own_client_id) && empty($seller->own_client_id) }
          {Html::a($smarty.const.IMAGE_CANCEL, $editLink, ['class' => 'btn btn-primary'])}
      {/if}
  {/if}
    {if !$seller->isNewRecord }
        {Html::a($smarty.const.PAYPAL_PARTNER_DELETE_SELLER, $unBoardLink, ['class' => 'btn btn-onboard btn-primary onboard-delete', 'style'=>'float:right'])}
    {/if}
  </div>
</div>
    
{if !$ownAPIDetails}
    <br/>
<div id="extraHeading" class="alert {if $seller->is_onboard}alert-info{else}alert-danger{/if}">
    {if $seller->is_onboard}
      {$smarty.const.PAYPAL_PARTNER_SELLER_BOARDED}
    {else}
      {$smarty.const.PAYPAL_PARTNER_SELLER_NOT_BOARDED}
    {/if}
</div>
{/if}

<script src="plugins/clipboard-js/clipboard.min.js"></script>
{include "./info.tpl"}
<div class="">
    <div class="col-md-4">
      {if !$ownAPIDetails}
        <div class="col-2">
          <label>{$smarty.const.PAYPAL_PARTNER_SELLER_TRACKING_ID}</label>
          {$params = ['class' => 'form-control', 'style' => 'width:100%', 'id' => 'txtTrackingId']}
          {if !$seller->isNewRecord}{$params['readonly'] = true}{/if}
          <div class="input-group">
              {Html::activeTextInput($seller, 'tracking_id', $params)}
              <div class="input-group-addon js-clipboard-copy" data-clipboard-target="#txtTrackingId" data-original-title="" title=""><i class="icon-copy"></i></div>
          </div>
        </div>
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
                {Html::activeTextInput($seller, 'email_address', ['class' => 'form-control'])}
            </label>
        </div>
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
{if !$ccpActive}{* ccp settings in second column*}
    </div>

    <div class="col-md-3">
{/if}
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

{if $ccpActive}{* ccp settings in second column*}
    <div class="col-md-3">
        <div class="col-full"><label>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_FIELDS}</label></div>
        <div class="col-2">
            <label>
                <span>{$smarty.const.ENTRY_STATUS}</span>
                {Html::checkbox('paypal_partner_ccp_status', $seller.paypal_partner_ccp_status, ['value' => 1, 'class'=>'check_on', 'onchange'=> '$("#ccp_block").toggle();'])}
            </label>
        </div>
        <div id='ccp_block' class="col-full" {if empty($seller->paypal_partner_ccp_status)}style="display:none{/if}">
            <div class="col-2">
                <h4>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DS}</h4>
                <div class="col-full modules-description">{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DS_DESCRIPTION}<br /></div>
                <label>
                    <span>{$smarty.const.ENTRY_STATUS}</span>
                    {Html::checkbox('paypal_partner_3ds_status', !isset($seller3DS['status']) || $seller3DS['status'], ['value' => 1, 'data-default' => $seller3DS['defaults']['status']])}
                </label>
            </div>
            <div class="col-2">
                <h6>{$smarty.const.TEXT_PAYPAL_PARTNER_CONTINGENCIES}<span class="colon">:</span></h6>
                <label>
                    {Html::radio('paypal_partner_3ds_contingencies',(!isset($seller3DS['contingencies']) || ( $seller3DS['contingencies'] == 'SCA_WHEN_REQUIRED')), ['value' => 'SCA_WHEN_REQUIRED'])}
                    <span>SCA_WHEN_REQUIRED</span>
                </label>
            </div>
            <div class="col-2">
                <label>
                    {Html::radio('paypal_partner_3ds_contingencies', isset($seller3DS['contingencies']) && $seller3DS['contingencies'] == 'SCA_ALWAYS', ['value' => 'SCA_ALWAYS'])}
                    <span>SCA_ALWAYS</span>
                </label><br />
            </div>

            <div class="col-2">
                <h4>{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DSA}</h4>
                <div class="col-full modules-description">{$smarty.const.TEXT_PAYPAL_PARTNER_CUSTOM_CARD_3DSA_DESCRIPTION}</div>
            </div>

                    
                                
            <div class="col-2">
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
        </div>
    </div>
{/if}

    <div class="col-md-5">
        <div class="col-2 ">
            {if is_array($sellerBoardingDetails)}
                {foreach $sellerBoardingDetails as $sbdGroup => $sbdData}
                    {*<div class="col-full"><span class="h4">{$sbdGroup}</span></div>*}
                    {if is_array($sbdData)}
                        {foreach $sbdData as $name => $value}
                    <div class="col-full {if $sbdGroup=='errors'}alert alert-danger{/if}{if $sbdGroup=='warnings'}alert alert-warning{/if}">
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
                    {/if}
                {/foreach}
            {/if}
        </div>
    </div>
</div>
    <script>
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
        /** /
        $('.btn-onboard').popUp({           
        });

         /**/
        $('.btn-onboard').on('click', function() {
            var url = this.href;
            var el = this;
            var confirmed = false;
            if ($(this).hasClass('onboard-delete')) {
                bootbox.confirm('{$smarty.const.TEXT_PAYPAL_PARTNER_UNLINK_PROMPT|escape:javascript}', function(result){
                    if (result) {
                        $.post(url, { }, function(data) {
                            if (data.reload==1) {
                                var search = '';
                                if ( data.params ) {
                                    search = data.params;
                                } else {
                                    try {
                                        search = location.search
                                            .replace(/[\?&]ppp_next=[^&]+/, '')
                                            .replace(/[\?&]action=[^&]+/, '')
                                            .replace(/^&/, '');
                                        } catch ( e ) { }
                                }
                                history.replaceState && history.replaceState(
                                    null, '', location.pathname + '?' + search );
                                window.location.reload();
                            } else {
                                $(el).parent().append('<div id="boardingPopup">' + '</div>');
                                $('#boardingPopup').popUp({ 'event' : 'show' });
                                $('.pop-up-content').html(data);
                                //alert(data);
                            }
                        }); //, 'json
                    }
                });
            } else {
                confirmed = true;
            }
            if (confirmed) {
                $.post(url, { }, function(data) {
                    if (data.reload==1) {
                        history.replaceState && history.replaceState(
                            null, '', location.pathname + location.search.replace(/[\?&]ppp_next=[^&]+/, '').replace(/^&/, '?')
                          );
                        window.location.reload();
                    } else {
                        $(el).parent().append('<div id="boardingPopup">' + '</div>');
                        $('#boardingPopup').popUp({ 'event' : 'show' });
                        $('.pop-up-content').html(data);
                        //alert(data);
                    }
                }); //, 'json'
            }
          return false;
    });/**/

        {if $gologin && !$cor}
          $('.btn-login').trigger('click');
          //$('#extraHeading').srollTo();
        {/if}

        $('#selectState').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{$app->urlManager->createUrl('customers/states')}",
                    dataType: "json",
                    data: {
                        term : request.term,
                        country : $("#selectCountry").val()
                    },
                    success: function(data) {
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
            select: function(event, ui) {
                $('input[name="city"]').prop('disabled', true);
                if(ui.item.value != null){ 
                    $('input[name="city"]').prop('disabled', false);
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
        });

        function set3DSDefault(){
            {* 2do print_r($seller3DS['defaults''], 1) *}
            var key = 'status';
            if ($('input[name="paypal_partner_3ds_'+key+'"]').length>0 && $('input[name="paypal_partner_3ds_'+key+'"]:checked').length==0) {
                $('input[name="paypal_partner_3ds_'+key+'"]').click();
            }
            var val = 'SCA_WHEN_REQUIRED';
            key = 'contingencies';
            if ($('input[name="paypal_partner_3ds_'+key+'"][value="'+val+'"]').length>0 && $('input[name="paypal_partner_3ds_'+key+'"][value="'+val+'"]:checked').length==0) {
                $('input[name="paypal_partner_3ds_'+key+'"][value="'+val+'"]').click();
            }
            
            $('input.c-3dsa.def-unchecked:checked, input.c-3dsa.def-checked:not(:checked)').click();

            return false;

        }
    </script>
