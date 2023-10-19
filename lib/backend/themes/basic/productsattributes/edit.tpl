{use class = "yii\helpers\Html"}
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<div class="properties">
{Html::beginForm(['productsattributes/attributesubmit'], 'post', ['name' => 'save_param_form'])}
{Html::hiddenInput('global_id', $global_id)}
{Html::hiddenInput('products_options_id', $products_options_id)}
{Html::hiddenInput('type_code', $type_code)}
<div class="prop_wrapper">
{if $type_code == 'option'}
    <div class="properties_top">
        <div class="properties_filter">
            <ul class="pf_ul after">
                <li>
                    <label>{$smarty.const.HEADING_TYPE}:</label>
                    {Html::dropDownList('type', $type, ['' => 'default', 'select' => 'select', 'radio' => 'radio'], ['class'=>'form-control'])}
                </li>
                <li class="property_option">
                    <label>{$smarty.const.TEXT_VIRTUAL}</label>
                    <input type="checkbox" name="is_virtual" value="1" class="check_on_off" {if $is_virtual > 0} checked="checked" {/if} />
                </li>
            </ul>
            <div class="pf_bottom after">
                <div class="pf_bottom_td">{$smarty.const.DISPLAY_MODE}</div>
                <div class="pf_bottom_td">
                    <label>{$smarty.const.TEXT_FILTER}</label>
                    <input type="checkbox" name="display_filter" value="1" class="check_on_off" {if {$display_filter > 0}} checked="checked" {/if} />
                </div>
                <div class="pf_bottom_td">
                    <label>{$smarty.const.TEXT_SEARCH}</label>
                    <input type="checkbox" name="display_search" value="1" class="check_on_off" {if {$display_search > 0}} checked="checked" {/if} />
                </div>
                <div class="pf_bottom_td" style="width:350px;">&nbsp;</div>
            </div>
        </div>
    </div>
{else}
    <div class="properties_top">
        <div class="properties_filter">
            <ul class="pf_ul after">
                <li>
                    <label>Extra field</label>
                    {Html::dropDownList('type', $type, ['' => 'none', '1' => 'text box', '2' => 'text area'], ['class'=>'form-control'])}
                </li>
            </ul>
        </div>
    </div>

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
                    <tr class="properties_descr">
                      <td class="pf_label">
                        {if $process_type == 'value'}
                            {$smarty.const.TABLE_HEADING_OPT_VALUE}
                        {else}
                            {$smarty.const.TABLE_HEADING_OPT_NAME}
                        {/if}
                    </td>
                      {if $lang['code'] == $default_language}
                      <td>{Html::textInput('option_name['|cat:$lang['id']|cat:']', $options[$lang['id']]['option_name'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'required'=>true])}</td>
                      {else}
                      <td>{Html::textInput('option_name['|cat:$lang['id']|cat:']', $options[$lang['id']]['option_name'], ['class'=>'form-control'])}</td>
                      {/if}
                    </tr>
                    {if $process_type == 'value'}
                    <tr>
                    <td class="pf_label">
                      {$smarty.const.TABLE_HEADING_OPT_VALUE_ALIAS}
                    </td>
                    {if $lang['code'] == $default_language}
                      <td>{Html::textInput('option_name_alias['|cat:$lang['id']|cat:']', $options[$lang['id']]['option_name_alias'], ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'required'=>false])}</td>
                      {else}
                      <td>{Html::textInput('option_name_alias['|cat:$lang['id']|cat:']', $options[$lang['id']]['option_name_alias'], ['class'=>'form-control'])}</td>
                      {/if}
                    </tr>
                    {/if}
                    <tr class="properties_descr">
                      <td class="pf_label">{$smarty.const.TEXT_ICON}</td>
                      <td>

                          <div class="upload-box upload-box-wrap"
                               data-name="option_image[{$lang['id']}]"
                               data-value="{$options[$lang['id']]['option_image']}"
                               data-upload="option_image_loaded[{$lang['id']}]"
                               data-delete="option_image_delete[{$lang['id']}]"
                               data-type="image"
                               data-acceptedFiles="image/*">
                          </div>

                      </td>
                    </tr>
                      <tr class="properties_descr">
                        <td class="pf_label">{$smarty.const.TEXT_COLOR_}:</td>
                        <td>
                            <div class="colors-inp">
                                <div id="cp3" class="input-group colorpicker-component">
                                  <input type="text" name="option_color[{$lang['id']}]" value="{$options[$lang['id']]['option_color']}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
                                  <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
                                </div>
                              </div>
                            
                        </td>
                      </tr>
                      
                    </table>
              </div>
          </div>
      {/foreach}
      </div>
    </div>
</div>
<div class="buttons after">
  <a href="{Yii::$app->urlManager->createUrl(['productsattributes/', 'type_code' => $type_code, 'global_id' => $global_id])}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
  <button class="btn btn-save">{$smarty.const.IMAGE_SAVE}</button>
</div>
</div>

{Html::endForm()}
<script type="text/javascript">
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
    onText: "{$smarty.const.SW_ON}",
    offText: "{$smarty.const.SW_OFF}",
    handleWidth: '38px',
    labelWidth: '24px'
  }
)

function changeDefaultLang(theInput, default_lang) {
  $('input[name^="' + theInput.name.replace('[' + default_lang + ']', '[') + '"]').each(function(index) {
    $(this).attr('placeholder', theInput.value);
  });
}

$(function() {
    var createColorpicker = function (){
      setTimeout(function(){
        var cp = $('.colorpicker-component:not(.colorpicker-element)');
        cp.colorpicker({ sliders: {
          saturation: { maxLeft: 200, maxTop: 200 },
          hue: { maxTop: 200 },
          alpha: { maxTop: 200 }
        }});

        var removeColorpicker = function() {
          cp.colorpicker('destroy');
          cp.closest('.popup-box-wrap').off('remove', removeColorpicker)
          $('.style-tabs-content').off('st_remove', removeColorpicker)
        };

        cp.closest('.popup-box-wrap').on('remove', removeColorpicker);
        $('.style-tabs-content').on('st_remove', removeColorpicker);
      }, 200)
    };
    
    createColorpicker();

    $('.upload-box').fileManager()
});
</script>
