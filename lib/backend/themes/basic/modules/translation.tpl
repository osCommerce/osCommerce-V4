{use class="yii\helpers\Html"}
<form id="saveModules" name="modules" action="{Yii::$app->request->getUrl()}" method="post"  onSubmit="return updateModule('{$codeMod}');">
<div class="btn-bar btn-bar-top after">
	<div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
</div>

        <div class="tab-pane" id="tab_1_1">
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs">
                        {foreach $languages as $lKey => $lItem}
                        <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_2_{$lItem['id']}"><a class="flag-span">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                        {/foreach}
                    </ul>
                    <div class="tab-content">
                        {foreach $params  as $dKey => $dItem}
                          <div class="tab-pane{if $dKey == 0} active{/if}" id="tab_2_{$dItem['id']}">
                            {*$dItem.id = null*}
                            {foreach $dItem  as $conKey => $cItem}
                            {if $conKey != 'id'}
                              <label>{$conKey}:</label>
                                <div class="edp-line">
                                    <label>{$cItem['configuration_title_label']}</label>
                                    {$cItem['configuration_title']}
                                </div>
                                <div class="edp-line">
                                    <label>{$cItem['configuration_desc_label']}</label>
                                    {$cItem['configuration_description']}
                                </div>
                              {/if}
                            {/foreach}
                          </div>
                        {/foreach}
                    </div>                    
                </div>                
        </div>

<div class="btn-bar edit-btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_UPDATE}</button></div>
</div>

</form>
<script type="text/javascript">

function updateModule(item_id) {
    $.post("modules/translation?{Yii::$app->request->getQueryString()}", $('form[name=modules]').serialize(), function (data, status) {
          if (status == "success") {
            global = item_id;
            $('body').append('<div class="popup-box-wrap pop-module pop-mess"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close pop-up-close-alert"></div><div class="pop-up-content"><div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div><div class="popup-content pop-mess-cont pop-mess-cont-success">{$smarty.const.TEXT_MODULES_SUCCESS}</div></div><div class="noti-btn"><div></div><div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div></div></div></div>'); 
                  $(window).scrollTop(10);
                  $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                      $(this).parents('.pop-mess').remove();
                  });            
          } else {
              alert("Request error.");
          }
    }, "html");
    return false;
}

function backStatement() { 
    window.history.back();      
    return false;
}
</script>