{use class="yii\helpers\Html"}
<form name="departments" id="new_department" action="{$app->urlManager->createUrl('departments/create')}" onSubmit="return check_form();" method="post">
<div class="box-wrap">
    <div class="create-or-wrap after create-cus-wrap">
        <div class="cbox-left">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_WEBSITE}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STORE_NAME}<span class="fieldRequired">*</span></label>{Html::textInput('departments_store_name', '', ['maxlength'=>'128', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_HTTP_SERVER}<span class="fieldRequired">*</span></label>{Html::textInput('departments_http_server', '', ['maxlength'=>'128', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])}.{$smarty.const.CPANEL_PARENT_DOMAIN}
                        </div>
                    </div>
                    <!--<div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Username:<span class="fieldRequired">*</span></label>{Html::textInput('username', '', ['maxlength' => '8', 'size' => '32', 'required' => true, 'class' => 'form-control'])}can not begin with test, number or .<br>can not contain - or _
                        </div>
                    </div>!-->
                    <div class="w-line-row w-line-row-1 widget-domain-alias">
                        <table class="tl-grid js-customer-multiemails">
                            <thead>
                                <tr>
                                    <th>Aliases (the same rules as domain)</th>
                                    <th class="actions delete-header">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody class="tbody" data-rows-count="0">
                                
                                
                            </tbody>
                            <tfoot class="tfoot" style="display: none">
                                <tr class="multi-emails-row">
                                    <td>{Html::input('text', '_unhide_domain_alias[%idx%]', '', ['class' => 'form-control'])}</td>
                                    <td><span class="remove-ast" onclick="multiEmailsDeleteRow(this);"></span></td>

                                </tr>
                            </tfoot>
                        </table>
                        &nbsp;
                        <div class="buttons_hours">
                            <button type="button" class="btn js-add-customer-multiemails" >{$smarty.const.TEXT_ADD_MORE}</button>
                        </div>
                    </div>
<script type="text/javascript">
function multiEmailsDeleteRow(el){
  $(el).parents('.multi-emails-row').remove();
}
$(document).ready(function(){
    
    $('.js-customer-multiemails').on('add_row',function(){
        var skelHtml = $('.widget-domain-alias .tfoot').html();
        var $body = $('.widget-domain-alias .tbody');
        var counter = parseInt($body.attr('data-rows-count'),10)+1;
        $body.attr('data-rows-count',counter);
        skelHtml = skelHtml.replace(/_unhide_/g,'',skelHtml);
        skelHtml = skelHtml.replace(/%idx%/g, counter,skelHtml);
        $body.append(skelHtml);
    });
    
    $('.js-add-customer-multiemails').on('click',function(){
        $('.js-customer-multiemails').trigger('add_row');
    });
});
</script>
                </div>
            </div>
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-theme"><h4>{$smarty.const.CATEGORY_API}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_API_KEY_DEPARTMENT}<span class="fieldRequired">*</span></label>{Html::textInput('api_key', $dInfo->api_key, ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control','id'=>'txtSiteApiKey', 'required' => true])}( <a href="javascript:void(0)" id="lnSiteApiKeyGenerate">{$smarty.const.TEXT_GENERATE}</a> )
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cbox-right">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_CONTACTS}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_FIRST_NAME}<span class="fieldRequired">*</span></label>{Html::textInput('departments_firstname', '', ['maxlength'=>'32', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_LAST_NAME}<span class="fieldRequired">*</span></label>{Html::textInput('departments_lastname', '', ['maxlength'=>'32', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<span class="fieldRequired">*</span></label>{Html::textInput('departments_email_address', '', ['maxlength'=>'96', 'size'=>'32', 'required'=>true, 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>{Html::textInput('departments_telephone', '', ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STREET_ADDRESS}</label>{Html::textInput('departments_street_address', '', ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_SUBURB}</label>{Html::textInput('departments_suburb', '', ['maxlength'=>'32', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_POST_CODE}</label>{Html::textInput('departments_postcode', '', ['maxlength'=>'80', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_CITY}</label>{Html::textInput('departments_city', '', ['maxlength'=>'80', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STATE}</label>{Html::textInput('departments_state', '', ['maxlength'=>'32', 'size'=>'32', 'class'=>'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COUNTRY}</label>{tep_draw_pull_down_menu('departments_country_id', \common\helpers\Country::get_countries(), STORE_COUNTRY, 'class="form-control"')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="btn-bar">
  <div class="btn-left"><a href="{$app->urlManager->createUrl('departments/index')}" class="btn btn-cancel-foot">Cancel</a></div>
  <div class="btn-right"><button class="btn btn-confirm">Insert</button></div>
</div>
</form>
<script language="javascript"><!--

$(document).ready(function(){
      $('#lnSiteApiKeyGenerate').on('click',function(){
        $.getJSON('{\Yii::$app->urlManager->createUrl('api/generate-key')}',function( data ) {
            if (data.api_key) $('#txtSiteApiKey').val(data.api_key);
        });
      });
    });
  function check_form() {
    var error = 0;
    var error_message = "{$smarty.const.JS_ERROR}";

    var departments_store_name = document.departments.departments_store_name.value;
    var departments_http_server = document.departments.departments_http_server.value;

    var departments_firstname = document.departments.departments_firstname.value;
    var departments_lastname = document.departments.departments_lastname.value;
    var departments_email_address = document.departments.departments_email_address.value;
//    var departments_telephone = document.departments.departments_telephone.value;
//    var departments_street_address = document.departments.departments_street_address.value;
//    var departments_postcode = document.departments.departments_postcode.value;
//    var departments_city = document.departments.departments_city.value;

    if (departments_store_name = "" || departments_store_name.length < 2) {
      error_message = error_message + "{$smarty.const.JS_STORE_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_http_server = "" || departments_http_server.length < 3) {
      error_message = error_message + "{$smarty.const.JS_HTTP_SERVER|escape:'javascript'}";
      error = 1;
    }

    if (departments_firstname = "" || departments_firstname.length < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_FIRST_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_lastname = "" || departments_lastname.length < {$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}) {
      error_message = error_message + "{JS_LAST_NAME|escape:'javascript'}";
      error = 1;
    }

    if (departments_email_address = "" || departments_email_address.length < {ENTRY_EMAIL_ADDRESS_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_EMAIL_ADDRESS|escape:'javascript'}";
      error = 1;
    }
/*
    if (departments_telephone = "" || departments_telephone.length < {$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_TELEPHONE|escape:'javascript'}";
      error = 1;
    }

    if (departments_street_address == "" || departments_street_address.length < {ENTRY_STREET_ADDRESS_MIN_LENGTH}) {
      error_message = error_message + "{JS_ADDRESS|escape:'javascript'}";
      error = 1;
    }

    if (departments_postcode == "" || departments_postcode.length < {$smarty.const.ENTRY_POSTCODE_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_POST_CODE|escape:'javascript'}";
      error = 1;
    }

    if (departments_city == "" || departments_city.length < {$smarty.const.ENTRY_CITY_MIN_LENGTH}) {
      error_message = error_message + "{$smarty.const.JS_CITY|escape:'javascript'}";
      error = 1;
    }

  {if ACCOUNT_STATE eq 'true'}
    if (document.departments.elements['departments_state'].type != "hidden") {
      if (document.departments.departments_state.value == '' || document.departments.departments_state.value.length < {$smarty.const.ENTRY_STATE_MIN_LENGTH}) {
        error_message = error_message + "{$smarty.const.JS_STATE|escape:'javascript'}";
        error = 1;
      }
    }
  {/if}
*/
    if (document.departments.departments_country_id.value == 0) {
      error_message = error_message + "{$smarty.const.JS_COUNTRY|escape:'javascript'}";
      error = 1;
    }

    if (error == 1) {
      alert(error_message);
      return false;
    } else {
      $('body').append('<div class="load_page"><div class="load_page_icon"></div></div>');
      $.post("{$app->urlManager->createUrl('departments/create')}", $('#new_department').serialize(), function(data, status) { 
        if (status == "success") {
          if (parseInt(data) > 0) {
            window.location.href = "{$app->urlManager->createUrl(['departments/edit', 'dID' => ''])}" + data;
          } else {
            $('.load_page').remove();
            $("#messageStack").html('<div class="alert alert-warning fade in"><i data-dismiss="alert" class="icon-remove close"></i>' + data + '</div>');
          }
        } else {
          alert("Request error.");
        }
      },"html");
      return false;
    }
  }

//--></script>
