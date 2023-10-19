{use class="yii\helpers\Html"}
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
{if not {$app->controller->view->usePopupMode}}
<div class=""><a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id|default}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
{/if}
<div class="properties">
  <form action="{Yii::$app->urlManager->createUrl('properties/save')}" method="post" enctype="multipart/form-data" id="property_edit" name="property_edit" {if {$app->controller->view->usePopupMode}} onsubmit="return saveProperty()" {/if}>
  {Html::hiddenInput('properties_id', $pInfo->properties_id|default:0)}
  {Html::hiddenInput('properties_type', 'category')}
  <div class="prop_wrapper">
  {if {$app->controller->view->usePopupMode}}
    <div class="properties_top">
      <div class="properties_filter popup_pr_filter">
        <strong>{$smarty.const.TEXT_CATEGORY}</strong>
        {tep_draw_pull_down_menu('parent_id', \common\helpers\Properties::get_properties_tree(), 0, 'class="form-control"')}
      </div>
    </div>  
  {else}
    {Html::hiddenInput('parent_id', $pInfo->parent_id)}
  {/if}
    <div class="properties_bottom tabbable-custom">
        {if count($languages) > 1}
      <ul class="nav nav-tabs under_tabs_ul">
        {foreach $languages as $lang}
          <li{if $lang['code'] == $default_language} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lang['code']}"><a>{$lang['logo']}<span>{$lang['name']}</span></a></li>
        {/foreach}
      </ul>
      {/if}
      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
        {foreach $languages as $lang}
          <div class="tab-pane{if $lang['code'] == $default_language} active{/if}" id="tab_{$lang['code']}">
            <div class="property_content_width">
              <table cellspacing="0" cellpadding="0">
                {if $lang['code'] == $default_language}
              <tr>
                <td class="pf_label">{$smarty.const.TEXT_SAME_OF_ALL}</td>
                <td><input type="checkbox" name="same_all_languages" value="1" class="check_on_off same_all"></td>
              </tr>
                {/if}
              <tr>
                <td class="pf_label">{$smarty.const.TEXT_NAME}</td>
                {if $lang['code'] == $default_language}
                <td>{Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), 'required'=>true])}</td>
                {else}
                <td>{Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), ['class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id'])])}</td>
                {/if}
              </tr>
              <tr class="use_additional">
                <td class="pf_label">{$smarty.const.TEXT_ADDITIONAL_INFO}</td>
                <td><input type="checkbox" name="additional_info" value="1" class="check_on_off check_desc" {if {$app->controller->view->additional_info|default > 0}} checked="checked" {/if}></td>
              </tr>
              <tr class="use_desc" {if {$app->controller->view->additional_info|default > 0}} style="display:table-row;" {else} style="display:none;" {/if}>
                <td class="pf_label">{$smarty.const.TEXT_DESCRIPTION}</td>
                <td><textarea name="properties_description[{$lang['id']}]" class="form-control">{\common\helpers\Properties::get_properties_description($pInfo->properties_id|default:null, $lang['id'])}</textarea></td>
              </tr>
            </table>
            </div>
          </div>
        {/foreach}
      </div>
      </div>
    </div>
    <div class="buttons after">
    {if not {$app->controller->view->usePopupMode}}
      <a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id|default:null}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
      <a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id|default:null}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
    {/if}
      <button class="btn btn-save">{$smarty.const.IMAGE_SAVE}</button>
    </div>
  </form>
</div>
<script type="text/javascript">
<!--
function disableClick() {
  $('.nav-tabs a').click(function() {
    return false
  });
}
function switchChange(var1, var2) {
  if ((var1.target.className == 'check_on_off check_desc') && var2 == true) {
    $('.use_desc').show();
    $('.check_desc').each(function() {
      if(!$(this).is(':checked')) {
        $(this).click();
      }
    })
  } else if ((var1.target.className == 'check_on_off check_desc') && var2 == false) {
    $('.use_desc').hide();
    $('.check_desc').each(function() {
      if($(this).is(':checked')) {
        $(this).click();
      }
    })
  } else if ((var1.target.className == 'check_on_off same_all') && var2 == true) {
    disableClick();
  } else if ((var1.target.className == 'check_on_off same_all') && var2 == false) {
    $('.nav-tabs a').off('click');
  }
}

$(".check_on_off").bootstrapSwitch(
  {
    onSwitchChange: function (element, arguments) {
      switchChange(element, arguments);
      return true;
    },
    onText: "{$smarty.const.TEXT_BTN_YES}",
    offText: "{$smarty.const.TEXT_BTN_NO}",
    handleWidth: '38px',
    labelWidth: '24px'
  }
)

function changeDefaultLang(theInput, default_lang) {
  $('input[name^="' + theInput.name.replace('[' + default_lang + ']', '[') + '"]').each(function(index) {
    $(this).attr('placeholder', theInput.value);
  });
}
//-->
</script>
