{use class="common\helpers\Html"}
<form id="saveModules" name="modules" onSubmit="return updateModule('{$codeMod}');" enctype="multipart/form-data" class="{$codeMod}">
  <input type="hidden" name="platform_id" id="page_platform_id" value="{$selected_platform_id}" />
  <input type="hidden" name="module" value="{$codeMod}">
  <input type="hidden" name="set" value="{$set}">
<div class="btn-bar btn-bar-top after ">
	<div class="btn-left"><a href="{Yii::$app->urlManager->createUrl(['modules', 'platform_id' => $selected_platform_id, 'set'=>$set, 'type'=>$type])}" {*onclick="return backStatement();"*} class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
{if $cancelUrl}
	<a href="{$cancelUrl}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
{else}
    {if $smarty.get['set']=='payment' && $smarty.get['module']=='paypal_partner' && !empty($app->controller->view->extra_params) > 0 && $installPPP }
        <div id="installPPPtop" class="div-installPPP " style="display:inline-block"><a class="btn btn-primary btn-no-margin" href="">{$smarty.const.ADD_PAYPAL}</a></div>
    {/if}
{/if}
    </div>
    {if (!$isExtension)}
	<div class="btn-right">
        <a class="btn btn-no-margin" href="{Yii::$app->urlManager->createUrl(['modules/export','platform_id' => $selected_platform_id, 'set' => $set, 'module' => $codeMod])}">{$smarty.const.TEXT_EXPORT_SETTINGS}</a>
        <a class="btn btn-no-margin btn-import" href="javascript:void(0);">{$smarty.const.TEXT_IMPORT_SETTINGS}</a>
        <a class="btn btn-edit btn-no-margin" target="_blank" href="{Yii::$app->urlManager->createUrl(['modules/translation','platform_id'=>$selected_platform_id,'set'=>$set, 'module' => $codeMod])}">{$smarty.const.IMAGE_BUTTON_TRANSLATE}</a>
    </div>
    {/if}
</div>
<div class="widget box box-no-shadow">
        <div class="widget-content edit-modules">
            <div class="row">
                <div class="col-md-6">
                    {if !empty($description)}
                        <div class="wg_title">{$smarty.const.TEXT_DESCRIPTION}</div>
                        {$description}<br><br>
                    {/if}
                    <div class="wg_title">{$smarty.const.TEXT_SETTINGS}</div>
                    {$mainKey}
                </div>
                <div class="col-md-6">
                    <div class="wg_title">{$smarty.const.TEXT_RESTRICTIONS}</div>
                    {$restriction}

                    {if !empty($translationsKeys) && is_array($translationsKeys)}
                    <div class="wg_title">{$smarty.const.BOX_TRANSLATION_TEXTS}</div>
                    <div class="col-md-12 tab-pane">
                        <div class="tabbable tabbable-custom">
                            <ul class="nav nav-tabs">
                                {foreach $languages as $lKey => $lItem}
                                <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_2_{$lItem['id']}">
                                    <a class="flag-span">{$lItem['image']}<span>{$lItem['name']}</span></a>
                                </li>
                                {/foreach}
                            </ul>
                            <div class="tab-content">
                                {foreach $translationsKeys  as $dKey => $dItem}
                                <div class="tab-pane{if $dKey == 0} active{/if}" id="tab_2_{$dItem['id']}">
                                  {foreach $dItem['keys']  as $cItem}
                                    <label>{$cItem['key']}:</label>
                                        <div class="edp-line">
                                            <label>{$cItem['configuration_title_label']}</label>
                                            {$cItem['configuration_title_field']}
                                        </div>
                                  {/foreach}
                                </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    {/if}

                </div>
            </div>
        </div>
</div>
{if is_array($platformKeys)}
<div class="widget box box-no-shadow">
	<div class="widget-header"><h4>{$smarty.const.PLATFORM_SETTINGS}</h4></div>
        <div class="widget-content edit-modules" id="module-platforms">

            <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
                <ul class="nav nav-tabs">
                    {foreach $platformKeys  as $platform}
                        <li id="tab_platform{$platform['id']}"><a><span>{$platform['text']}</span></a></li>
                    {/foreach}
                </ul>
                <div class="tab-content tab-content1 widget-content">

                <!--div class="tab-pane topTabPane tabbable-custom active"-->

                {foreach $platformKeys  as $platform}
                    <div id="page_platform{$platform['id']}" {if $tab!='tab_connection'}style="display: none;"{/if}>
                        <!---div class="widget box"--->
                            <div class="widget-content edit-modules">
                                {$platform['html']}
                            </div>
                    </div>
                {/foreach}
                </div>
            </div>
        </div>
        </div>
</div>
{/if}
{if {strlen($app->controller->view->extra_params)} > 0}
<div class="widget box box-no-shadow">
	<div class="widget-header"><h4>{$smarty.const.IMAGE_DETAILS}</h4></div>
        <div class="widget-content edit-modules" id="module-extra">{$app->controller->view->extra_params}</div>
</div>
{/if}
<div class="btn-bar edit-btn-bar">
    <div class="btn-left"><a href="{Yii::$app->urlManager->createUrl(['modules', 'platform_id' => $selected_platform_id, 'set'=>$set, 'type'=>$type])}" {*onclick="return backStatement();"*} class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
{if $cancelUrl}
	<a href="{$cancelUrl}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
{/if}
{*<a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a>*}</div>
    <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_UPDATE}</button></div>
</div>

</form>
{if $smarty.get['set']=='payment' && $smarty.get['module']=='paypal_partner' && !empty($app->controller->view->extra_params) > 0}
    {include file="./ppp_js.tpl"}
{/if}

<script type="text/javascript">
function backStatement() { 
    window.history.back();      
    return false;
}
function changeModule( item_id, action) {
  var process_changes = function(){
    $.post("modules/change", {
      'set': '{$set}',
      'platform_id': '{$selected_platform_id}',
      'module': item_id,
      'action': action
    }, function (response, status) {
        alert(status);
        alert(response);
      if (status == "success") {
        global = item_id;
        checkPPP('{$selected_platform_id}');
        if(action == 'remove'){
          backStatement();
        }
      } else {
        alert("Request error.");
      }
    }, "json")
    .fail(function(jqXHR){
        alert('{$smarty.const.TEXT_GENERAL_ERROR}\nServer error: '+jqXHR.status);
    });
  }
  if ( action=='remove' ) {
    bootbox.dialog({
      message: "{$smarty.const.TEXT_MODULES_REMOVE_CONFIRM}",
      title: "{$smarty.const.TEXT_MODULES_REMOVE_CONFIRM_HEAD}",
      buttons: {
        success: {
          label: "{$smarty.const.JS_BUTTON_YES}",
          className: "btn-delete",
          callback: process_changes
        },
        main: {
          label: "{$smarty.const.JS_BUTTON_NO}",
          className: "btn-cancel",
          callback: function () {
            //console.log("Primary button");
          }
        }
      }
    });
  }else{
    process_changes();
  }
  return false;
}

function Resp(item_id, message){
    global = item_id;
    var autoClose = false;
    if (message=='') {
      message = '<div class="popup-content pop-mess-cont pop-mess-cont-success">{$smarty.const.TEXT_MODULES_SUCCESS|escape}</div>';
      autoClose = true;
    }
    $('body').append('<div class="popup-box-wrap pop-module pop-mess"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close pop-up-close-alert"></div><div class="pop-up-content"><div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>' + message +'</div><div class="noti-btn"><div></div><div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div></div></div></div>');
    $(window).scrollTop(10);
    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
        $(this).parents('.pop-mess').remove();
    });

    if (autoClose) {
        var tm = 2.5;
        if ($("#module-extra").length > 0) {
            tm = 1;
        }
        setTimeout(function(){
            if ($("#module-extra").length > 0) {
                /**/
                try {
                    location.hash = '#extra';
                } catch ( e ) { }
             /** /
                history.replaceState && history.replaceState(
                        null, '', location.pathname + location.search +'#extra'
                      );
             */
                window.location.reload();
            } else {
                $('.pop-mess').remove();
            }

        }, tm*1000);
    }

}
var files = [];
function updateModule(item_id) {
    var data = $('form[name=modules]').serializeArray();
    var fD = new FormData();
    var hasFile = false;
    if (data.length){
        $.each(data, function(i, e){
            fD.append(e.name, e.value);
        })
        
        if (Array.isArray(files)){
            $.each(Object.keys(files), function (i, key ){ 
                fD.append(key, files[key], files[key].name);
            });
            hasFile = true;
        }
    }
    
    if (!hasFile){
        $.post("modules/save?set={$set}", fD, function (data, status) {
            if (status == "success") {
                Resp(item_id, data);
            } else {
                alert("Request error.");
            }
            
        }, "html");
    } else {
        xhr = new XMLHttpRequest();
        
        xhr.open( 'POST', "modules/save?set={$set}", true );
        xhr.onreadystatechange = function ( response ) {
            if (this.readyState == 4 && this.status == 200) {
                Resp(item_id, this.responseText);
            }
        };
        xhr.send( fD );
    }
    return false;
}
function pageFromTab(tab) {
    return $(tab.attr('id').replace("tab_", "#page_"));
}
function tabClick(clicked) {
    if (!clicked.hasClass('active')) {
        tab = clicked.attr('id');

        active_tab = $('ul.nav-tabs').find('li.active');
        if(active_tab.length != 0) {
            active_tab.removeClass('active');
            pageFromTab(active_tab).hide();
        }

        clicked.addClass('active');
        pageFromTab(clicked).show();
    }
    return false;
}
{if is_array($platformKeys)}
$(document).ready(function() {
    $('ul.nav-tabs').find('li').unbind('click').bind('click', function() { tabClick($(this)) });
    $('ul.nav-tabs').find('li')[0].click();

});
{/if}

    $('.btn-import').each(function() {
        $(this).dropzone({
            url: '{Yii::$app->urlManager->createUrl(['modules/import','platform_id' => $selected_platform_id, 'set' => $set, 'module' => $class])}',
            acceptedFiles: "application/json",      
            success: function(){
                $('.dz-complete').hide();
                window.location.reload();
            }
        });
    });

</script>
