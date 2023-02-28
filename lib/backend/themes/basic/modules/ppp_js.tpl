{use class="common\helpers\Html"}
<script type="text/javascript">


var pppBaseHrefLive="", pppBaseHrefSandbox="", pppFetchKeysUrl = '';
var pppgolive = false, ppp_active=false, ppp_mode = 'Live', ppp_psi_selected=0, ppp_psi_id = [0, 0], hrefOwnExtra = '';
var ppppage = '{Yii::$app->controller->action->id}';

{* only on list - 1 button*}
function checkPPP(platform_id) {
      {if $smarty.get.set != 'payment'}
          return false;
      {/if}
    var post_data = [];
    post_data.push({ name: 'platform_id', value: platform_id});

    $.post("{Yii::$app->urlManager->createUrl(['modules/ppp-status', 'set' => 'payment', 'type' => 'online'])}", post_data, function (data, status) {
        if (status == "success") {
            if (data.titlePPP) {
                $('#installPPP a').text(data.titlePPP);
            }
            if (data.golivePPP) {
                $('#installPPP a').addClass('go-live');
            } else {
                $('#installPPP a').removeClass('go-live');
            }
            if (data.installPPP) {
               $('#installPPP').show();
            } else {
               $('#installPPP').hide();
            }
            ppp_active = data.activePPP;
        }
    }, "json");
}

function onboardedCallback(authCode, sharedId) {
    var temp_mode = '';
    if ($('#ppp_seller_mode').length) {
        temp_mode = $('#ppp_seller_mode').val();
    }
    fetch(pppFetchKeysUrl, {
      method: 'POST',
      headers: {
        'content-type': 'application/json'
      },
      body: JSON.stringify({
        authCode: authCode,
        sharedId: sharedId,
        platform_id: $('#page_platform_id').val(),
        ppp_mode: ppp_mode,
        psi_id: ppp_psi_selected,
      })

    }).then(function(res) {
        if (!res.ok) {
            alert("Something went wrong!");
        } else {
            //PAYPAL.apps.Signup.MiniBrowser.closeFlow();
        }
    });
}

////PayPal button in the wizard
function initPPPButton() {
    var pppIntervalId = window.setInterval(function() {
        if (PAYPAL) {
            if (typeof PAYPAL.apps.Signup.MiniBrowser.init !== 'function' ) {
                return false;
            }
            // Clear the interval id
            window.clearInterval(pppIntervalId);
            PAYPAL.apps.Signup.MiniBrowser.init();
            var pppIntervalId1 = window.setInterval(function() {
                var pppinited = false;
                try {
                    var attr = $($('.ppp-boarding-btn')[0]).css('display');
                    if (typeof attr !== 'undefined' && attr !== false  && attr !== '') {
                        pppinited = true;
                    } else {
                        return false;
                    }
                } catch ( e ) {
                    console.log(e);
                }

                window.clearInterval(pppIntervalId1);

                $('.ppp-boarding-widget').removeClass('ppp-boarding-widget-wait');
                $('.ppp-boarding-btn').prop('disabled', false);

            }, 500);
        }
    }, 500);
}

function pppWizardPopUp(){
    bootbox.dialog({
      title: '{$smarty.const.ADD_PAYPAL_TITLE|escape:javascript}',
      message: '<div class="bootbox-radiobutton-list ppp-quick-setup">'
+'<div class="ppp_step1">'
    + '<div class="ppp-setup-intro after">{$smarty.const.PAYPAL_EXISTING_ACCOUNT|escape:javascript}</div>'
    + '<div class="form-check-radio"><label class="form-check-label"><input class="form-check-input bootbox-input bootbox-input-radio" type="radio" name="bootbox-radio" value="1">{$smarty.const.PAYPAL_ACCOUNT_OPTIONS_YES|escape:javascript}</label></div>'
    + '<div class="form-check-radio"><label class="form-check-label form-check-radio-label"><input class="form-check-input bootbox-input bootbox-input-radio" type="radio" name="bootbox-radio" value="2">{$smarty.const.PAYPAL_ACCOUNT_OPTIONS_NO|escape:javascript}</label></div>'
+'</div>' // step 1

      //+ '<br><div id="ppp_advanced" class="ppp-advanced-link" style="display:none"><a href="javascript:void(0)" onclick="$(\'.ppp-popup-advanced\').toggle(); return false;">{$smarty.const.TEXT_ADVANCED|escape:javascript}</a></div>'

//step 2 no account
      + '<div class="ppp_small" style="display:none"><label class="form-check-label ppp-sandbox-option"><input class="form-check-input bootbox-input bootbox-input-checkbox ppp-test-mode" type="checkbox" name="test_mode" value="1">'
      + '<span class="ppp_small_label">{$smarty.const.PAYPAL_SANDBOX_TRY|escape:javascript}</span>' + '</label></div>'


//step 2 account
      + '<div class="ppp-popup-advanced" style="display:none">'
      + '<div class="form-check-radio"><label class="form-check-label"><input class="form-check-input bootbox-input bootbox-input-radio" type="radio" name="bootbox-radio" value="4">{$smarty.const.PAYPAL_ACCOUNT_OPTIONS_OWN_API_ACCESS|escape:javascript}</label></div>'
      + '<div class="form-check-radio"><label class="form-check-label"><input class="form-check-input bootbox-input bootbox-input-radio" type="radio" name="bootbox-radio" value="5">{$smarty.const.PAYPAL_ACCOUNT_PRESS_BUTTON|escape:javascript}</label><span class="paypal-button-holder ppp-boarding-widget ppp-boarding-widget-wait"><span class="ppp-over"></span><span>'
     + '<a disabled data-paypal-onboard-complete="onboardedCallback" href="#" data-paypal-button="PPLtBlue" class="ppp-boarding-btn" id="ppp-boarding-btn">{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</a>'
      +'</span></span> {$smarty.const.PAYPAL_PARTNER_GET_DATA_PAYPAL}</div>'
      + '<label class="form-check-label ppp-sandbox-option"><input class="form-check-input bootbox-input bootbox-input-checkbox ppp-test-mode" type="checkbox" name="test_mode" value="1">'
      + '{$smarty.const.PAYPAL_SANDBOX_MODE|escape:javascript}' + '</label></div><br>'
      + '</div>'
      + '</div>',



      onShow: function (e) {
            //get URLs
            var pppIntervalBaseUrl = window.setInterval(function() {

                if (pppBaseHrefLive == '') {
                    return false;
                }
                // Clear the interval id
                window.clearInterval(pppIntervalBaseUrl);
                $(".ppp-popup-advanced .ppp-boarding-btn").attr('href', pppBaseHrefLive);
            });

            //load PayPal lib
            (function(d, s, id){
            var js, ref = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)){
            js = d.createElement(s); js.id = id; js.async = true;
            js.src = "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
            ref.parentNode.insertBefore(js, ref); }
            }(document, "script", "paypaljs"));

            //init switchers
            $('.ppp-test-mode').on('click', function(){
                /// switch (sandbox/live) mode
////2do DO not switch!!!!! only add params
/*
                var post_data = [];
                    post_data.push({ name: 'platform_id', value: $('#page_platform_id').val()});
                    post_data.push({ name: 'set', value: '{$set}'});
                    post_data.push({ name: 'test_mode', value: $('input[name="test_mode"]:visible').is(':checked')});
                    //post_data.push({ name:'enabled', value: 'on' });
                    post_data.push({ name: 'module', value: 'paypal_partner'});
                    post_data.push({ name: 'action', value: 'install'});
                    //post_data.push({ name: 'golive', value: $('#installPPP a').hasClass('go-live')});

                $.post("{Yii::$app->urlManager->createUrl('modules/ppp-install')}", post_data, function (data, status) {
                  if (status == "success") {
                        if ($('.ppp-quick-setup .bootbox-input-radio:checked:visible').val() == '4') {
                            $('.ppp-boarding-widget').addClass('ppp-boarding-widget-wait');
                            $('.ppp-boarding-btn').prop('disabled', true);
                        } else if ($('.ppp-quick-setup .bootbox-input-radio:checked:visible').val() == '5') {
                            $('.ppp-boarding-widget').removeClass('ppp-boarding-widget-wait');
                            $('.ppp-boarding-btn').prop('disabled', false);
                        }
                  } else {
                    alert("Request error.");
                  }
                }, "json");
*/
                //replace paypal button
                try {
                    $('.ppp-boarding-btn').prop('disabled', true);
                }catch (e ) {
                    console.log(e);
                }
                var href = '';
                $('.ppp-boarding-widget').addClass('ppp-boarding-widget-wait');

                if (this.checked) {
                    href = pppBaseHrefSandbox;
                    ppp_mode = 'Sandbox';
                    ppp_psi_selected = ppp_psi_id[1];
                } else {
                    href = pppBaseHrefLive;
                    ppp_mode = 'Live';
                    ppp_psi_selected = ppp_psi_id[0];
                }
                hrefOwnExtra = '&platform_id=' + $('#page_platform_id').val() +  '&psi_id=' + ppp_psi_selected;
                $(".ppp-boarding-btn:visible").attr('href', href);
                initPPPButton();

            });

        //$('.bootbox-radiobutton-list div.form-check.radio').removeClass('radio');
        $('.ppp-quick-setup .bootbox-input-radio').on('click', function() {
            $('.ppp-quick-setup .form-check-radio').removeClass('active');
            $(this).parent().parent().removeClass('active');

        });
        $('.ppp-quick-setup .bootbox-input-radio').on('change', function(){
            $('.modal .btn').removeClass('disabled');
            if($(this).val() == 4){
                var baseHrefOwn = '{Yii::$app->urlManager->createUrl(['modules/edit', 'set'=>'payment', 'module'=>'paypal_partner', 'ppp_next'=>4])}';
                $('.modal-footer').html('<a href="' + baseHrefOwn + hrefOwnExtra + '#extra" class="btn btn-enter">{$smarty.const.TEXT_ENTER_API_DETAILS}</a>');
                $('.ppp-boarding-widget').addClass('ppp-boarding-widget-wait');
                $('.ppp-boarding-btn').prop('disabled', true);

            } else if($(this).val() == 5) {
                $('.modal-footer a.btn-enter').remove();
                $('.ppp-boarding-widget').removeClass('ppp-boarding-widget-wait');
                $('.ppp-boarding-btn').prop('disabled', false);
            }


        })
        if (pppgolive) {
            $(".ppp-sandbox-option").hide();
        }

      },

      buttons: {
        cancel: {
            label: "{$smarty.const.IMAGE_CANCEL}",
            className: "btn-cancel disabled hidden",
            callback: function () {
                $('.ppp_step1').show();
                $('.ppp_small').hide();
                $('.btn-next').show();
                $('.btn-cancel').hide();
                $('.ppp-modal-button').remove();
                return false;
            }
        },
        success: {
            label: "{$smarty.const.TEXT_NEXT}",
            className: "btn btn-next disabled",
            callback: function () {
                        var result = 0;
                        try {
                            result = parseInt($('.ppp-quick-setup input[name="bootbox-radio"]:checked').val());
                        } catch (e ) {
                        }

                        if(result == 1){
                            $('.ppp_step1').hide();
                            $('.ppp-popup-advanced').show();
                            $('.btn-next').hide();

                        }else if(result == 2){
                            $('.ppp_step1').hide();
                            $('.ppp_small').show();
                            $('.btn-next').hide();
                            $('.btn-cancel').removeClass('hidden');
                            $('.btn-cancel').show();
                            $('.btn-cancel').after(
                            '<div class="ppp-modal-button"><span class="paypal-button-holder ppp-boarding-widget ppp-boarding-widget-wait"><span class="ppp-over"></span><span>'
     + '<a disabled data-paypal-onboard-complete="onboardedCallback" href="' + pppBaseHrefLive + '" data-paypal-button="PPLtBlue" class="ppp-boarding-btn" id="ppp-boarding-btn-f">{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</a></div>'
      +'</span></span>');
                        }
                        initPPPButton();
///useless??? already installed - else - no links
                        /*if (result > 0) {
                          var post_data = [];
                          post_data.push({ name: 'platform_id', value: $('#page_platform_id').val()});
                          post_data.push({ name: 'ppp_next', value: result});
                          post_data.push({ name: 'set', value: '{$set}'});
                          post_data.push({ name: 'test_mode', value: $('input[name="test_mode"]:visible').is(':checked')});
                          //post_data.push({ name:'enabled', value: 'on' });
                          post_data.push({ name: 'module', value: 'paypal_partner'});
                          post_data.push({ name: 'action', value: 'install'});
                          post_data.push({ name: 'golive', value: $('#installPPP a').hasClass('go-live')}); // all buttons should have the class, and different container Ids

                          $.post("{Yii::$app->urlManager->createUrl('modules/ppp-install')}", post_data, function (data, status) {
                            if (status == "success") {
                                initPPPButton();
                              //resetStatement();
                            } else {
                              alert("Request error.");
                            }
                          }, "json");

                        }*/
                        return false;

                    }
                }
            },
    })
}


$(document).ready(function () {

    {if !empty($installPPP) }
        {if !empty($titlePPP)}
              $('.div-installPPP a, #installPPP a').text("{$titlePPP|escape:javascript}");
        {/if }
        {if !empty($golivePPP)}
              $('.div-installPPP a, #installPPP a').addClass('go-live');
        {/if }
    {/if}
    {if !empty($activePPP) }
        ppp_active = {$activePPP};
    {/if}

    //#installPPP a - listing
    //.div-installPPP a - edit (2pcs)
    $('.div-installPPP a, #installPPP a').on('click', function (event) {

        event.preventDefault();
        try {
            pppgolive = $(event.target).hasClass('go-live');
        } catch ( e ) { console.log(e); }

        pppBaseHrefLive=""; pppBaseHrefSandbox="";
        pData = { platform_id: $('#page_platform_id').val()}
        if ($('#psi_id').length > 0) {
            pData.psi_id = $('#psi_id').val();
        }

        var post_data = [];
        post_data.push({ name: 'platform_id', value: $('#page_platform_id').val()});
        post_data.push({ name: 'set', value: '{$set}'});
        post_data.push({ name: 'module', value: 'paypal_partner'});
        post_data.push({ name: 'action', value: 'install'});

// install module - we need it's installed before boarding so sync request
        if (!ppp_active) {
            $.ajax({
                url: '{Yii::$app->urlManager->createUrl('modules/ppp-install')}',
                data: post_data,
                async: false,
                type: 'POST',
                success: function(data){ }
            });
        }

        pData.curUrl = window.location.origin + window.location.pathname;

        $.post("{$boardingOptionsUrl}", pData, function (data, status) {

            if (status == "success") {
                pppBaseHrefLive = data.live;
                pppBaseHrefSandbox = data.sandbox;
                pppFetchKeysUrl = data.fetchKeysUrl;
                ppp_psi_id = [data.psi_id, data.spsi_id];
                hrefOwnExtra = '&platform_id=' + $('#page_platform_id').val() +  '&psi_id=' + ppp_psi_id[0];

                $(".ppp-popup-advanced .ppp-boarding-btn").attr('href', pppBaseHrefLive);
            } else {
                alert("Request error.");
            }
        }, "json");

        pppWizardPopUp();

    });
    
    {if $smarty.get.setup == 1 }
        $('#installPPP a').click();
    {/if}

});
  </script>
