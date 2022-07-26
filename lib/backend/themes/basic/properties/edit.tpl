{use class="common\helpers\Html"}
<link href="{$app->view->theme->baseUrl}/css/properties-edit.css" rel="stylesheet" type="text/css" />
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
{if not {$app->controller->view->usePopupMode}}
<div class=""><a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id|default:null}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
{/if}
<div class="properties">
  <form action="{Yii::$app->urlManager->createUrl('properties/save')}" method="post" enctype="multipart/form-data" id="property_edit" name="property_edit" {if {$app->controller->view->usePopupMode}} onsubmit="return saveProperty()" {/if}>
  {Html::hiddenInput('properties_id', $pInfo->properties_id|default:0)}

    <div class="widget box">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_SETTINGS}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
      </div>
      <div class="widget-content">


        <div class="properties_top">

          <div class="row">
            <div class="col-md-4 form-container">
              <h4>{$smarty.const.BOX_LOCALIZATION_LANGUAGES}</h4>

                  <div class="row">
                    <div class="col-md-4"><label>{$smarty.const.TEXT_SAME_OF_ALL}</label></div>
                    <div class="col-md-8"><input type="checkbox" name="same_all_languages" value="1" class="check_on_off same_all"></div>
                  </div>

            </div>
            <div class="col-md-4 form-container border-left padding-x-4 padding-b-2">

              <h4>{$smarty.const.TEXT_PROPERTIES_MAIN_OPTIONS}</h4>

              <div class="row">
                <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_TYPE}</label></div>
                <div class="col-md-8">{Html::dropDownList('properties_type', $pInfo->properties_type|default:null, $app->controller->view->properties_types, ['onchange'=>'changePropertyType()',  'class'=>'form-control', 'required'=>true])}</div>
              </div>
              <div class="row property_option">
                <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_OPTION}</label></div>
                <div class="col-md-8">{Html::dropDownList('multi_choice', $pInfo->multi_choice|default:null, $app->controller->view->multi_choices, ['class'=>'form-control'])}</div>
              </div>
              <div class="row property_format" style="display:none;">
                <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_FORMAT}</label></div>
                <div class="col-md-8">{Html::dropDownList('multi_line', $pInfo->multi_line|default:null, $app->controller->view->multi_lines, ['onchange'=>'changePropertyType()', 'class'=>'form-control'])}
                    {Html::dropDownList('decimals', $pInfo->decimals|default:null, $app->controller->view->decimals, ['class'=>'form-control'])}</div>
              </div>
              <div class="row property_filter_by" >
                <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_FILTER_BY_PROPERTY}:</label></div>
                <div class="col-md-8">{Html::dropDownList('filter_by_property', $pInfo->filter_by_property|default:null, $app->controller->view->filter_by_property, ['onchange'=>'changePropertyFilter()'])}</div>
              </div>
              <div id="div_property_filter_steps" class="row property_filter_steps" {if $pInfo->filter_by_property|default:null==0 }style="display:none"{$on_off_class="_on_off"}{else}{$on_off_class="check_on_off"}{/if}>
                <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_FILTER_STEPS}:</label></div>
                <div class="col-md-8">{Html::checkbox('filter_steps', $pInfo->filter_steps|default:null, ['class'=> $on_off_class, 'value'=>1])}</div>
              </div>
                {if {$app->controller->view->usePopupMode}}
                  <div class="row">
                    <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_CATEGORY}</label></div>
                    <div class="col-md-8">{tep_draw_pull_down_menu('parent_id', \common\helpers\Properties::get_properties_tree(), $pInfo->parent_id, 'class="form-control"')}</div>
                  </div>
                {else}
                    {Html::hiddenInput('parent_id', $pInfo->parent_id)}
                {/if}


            </div>
            <div class="col-md-4 form-container border-left padding-b-2">

              <h4>{$smarty.const.DISPLAY_MODE}</h4>

              <div class="row">
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_PRODUCT_INFO}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="display_product" value="1" class="check_on_off" {if {$pInfo->display_product|default:null > 0}} checked="checked" {/if} />
                </div>
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_LISTING}</label>
                </div>
                <div class="col-md-2"><input type="checkbox" name="display_listing" value="1" class="check_on_off" {if {$pInfo->display_listing|default:null > 0}} checked="checked" {/if} />
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_FILTER}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="display_filter" value="1" class="check_on_off" {if {$pInfo->display_filter|default:null > 0}} checked="checked" {/if} />
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-4 text-right">
                  {Html::dropDownList('display_filter_as',$pInfo->display_filter_as|default:null, ['' => TEXT_CHECKBOX, 'radio' => TEXT_RADIO, 'pulldown' => TEXT_PULLDOWN])}
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_SEARCH}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="display_search" value="1" class="check_on_off" {if {$pInfo->display_search|default:null > 0}} checked="checked" {/if} />
                </div>
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_COMPARE}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="display_compare" value="1" class="check_on_off" {if {$pInfo->display_compare|default:null > 0}} checked="checked" {/if} />
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_PRODUCTS_GROUPS}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="products_groups" value="1" class="check_on_off" {if {$pInfo->products_groups|default:null > 0}} checked="checked" {/if} />
                </div>
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_SWATCHES}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="display_as_image" value="1" class="check_on_off" {if {$pInfo->display_as_image|default:null > 0}} checked="checked" {/if} />
                </div>
              </div>


              <div class="row">
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_EXTRA_VALUES}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="extra_values" value="1" class="check_on_off" {if {$pInfo->extra_values|default:null > 0}} checked="checked" {/if} />
                </div>
                <div class="col-md-4 text-right">
                  <label>{$smarty.const.TEXT_RANGE_SELECT}</label>
                </div>
                <div class="col-md-2">
                  <input type="checkbox" name="range_select" value="1" class="check_on_off" {if {$pInfo->range_select|default:null > 0}} checked="checked" {/if} />
                </div>
              </div>


            </div>
          </div>

        </div>


      </div>
    </div>


  <div class="prop_wrapper">

    <div class="properties_bottom tabbable-custom">
      {if count($languages) > 1}
      <ul class="nav nav-tabs under_tabs_ul lang-tabs">
        {foreach $languages as $lang}
          <li{if $lang['code'] == $default_language} class="active"{/if}><a href="#tab_{$lang['code']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>
        {/foreach}
      </ul>
      {/if}
      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
        {foreach $languages as $lang}
          <div class="tab-pane{if $lang['code'] == $default_language} active{/if}" id="tab_{$lang['code']}">



            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.TEXT_PROPERTY}</h4>
                <div class="toolbar no-padding">
                  <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                  </div>
                </div>
              </div>
              <div class="widget-content">


                <div class="row">
                  <div class="col-md-4 form-container">
                    <h4>{$smarty.const.UPLOAD_IMAGE}</h4>

                    <div class="row properties_descr">
                      <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_ICON}</label></div>
                      <div class="col-md-8">
                        <div class="gallery-filedrop-container">
                          <div class="gallery-filedrop">
                            <span class="gallery-filedrop-message"><span>{$smarty.const.TEXT_DRAG_DROP}</span><a href="#gallery-filedrop" class="gallery-filedrop-fallback-trigger btn" rel="nofollow">{$smarty.const.TEXT_CHOOSE_FILE}</a></span>
                            <input size="30" id="gallery-filedrop-fallback-{$lang['id']}" name="properties_image[{$lang['id']}]" class="elgg-input-file hidden" type="file">
                            <input type="hidden" name="properties_image_loaded[{$lang['id']}]" class="elgg-input-hidden">

                          </div>

                          <div class="gallery-filedrop-queue">
                            <img style="max-height:48px;{if strlen(\common\helpers\Properties::get_properties_image($pInfo->properties_id|default:null, $lang['id'])) == 0}display:none;{/if}" src="{$smarty.const.DIR_WS_CATALOG_IMAGES}{\common\helpers\Properties::get_properties_image($pInfo->properties_id|default:null, $lang['id'])}" class="properties_image" />
                          </div>

                          <div class="hidden" id="image_wrapper">
                            <div class="gallery-template">
                              <div class="gallery-media-summary">
                                <div class="gallery-album-image-placeholder">
                                  <img src="">
                                  <span class="elgg-state-uploaded"></span>
                                  <span class="elgg-state-failed"></span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                  <div class="col-md-4 form-container border-left padding-x-4 padding-b-2">

                    <h4>{$smarty.const.TEXT_NAME_SEO}</h4>

                    <div class="row">
                      <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_NAME}</label></div>
                      <div class="col-md-8">
                          {if $lang['code'] == $default_language}
                            {Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), 'required'=>true])}
                          {else}
                            {Html::textInput('properties_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id']), ['class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name($pInfo->properties_id|default:null, $lang['id'])])}
                          {/if}
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_NAME_ALT}</label></div>
                      <div class="col-md-8">
                          {if $lang['code'] == $default_language}
                            {Html::textInput('properties_name_alt['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name_alt($pInfo->properties_id|default:null, $lang['id']), ['onchange'=>'changeDefaultLang(this, '|cat:$lang['id']|cat:')', 'class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name_alt($pInfo->properties_id|default:null, $lang['id'])])}
                          {else}
                            {Html::textInput('properties_name_alt['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_name_alt($pInfo->properties_id|default:null, $lang['id']), ['class'=>'form-control', 'placeholder'=>\common\helpers\Properties::get_properties_name_alt($pInfo->properties_id|default:null, $lang['id'])])}
                          {/if}
                      </div>
                    </div>

                    <div class="row properties_descr">
                      <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_SEO_PAGE_NAME}</label></div>
                      <div class="col-md-8">{Html::textInput('properties_seo_page_name['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_seo_page_name($pInfo->properties_id|default:null, $lang['id']), ['class'=>'form-control'])}</div>
                    </div>

                    <div class="row">
                      <div class="col-md-4 text-right"><label>{$smarty.const.TEXT_COLOR_}:</label></div>
                      <div class="col-md-3">
                        <div class="colors-inp">
                          <div id="cp3" class="input-group colorpicker-component">
                              {Html::textInput('properties_color['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_color($pInfo->properties_id|default:null, $lang['id']), ['class'=>'form-control'])}
                            <span class="input-group-addon"><i></i></span>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-2 text-right properties_descr"><label>{$smarty.const.TEXT_UNITS}</label></div>
                      <div class="col-md-3 properties_descr">
                        <div class="f_td_group unit_group_{$lang['id']}">{Html::textInput('properties_units_title['|cat:$lang['id']|cat:']', \common\helpers\Properties::get_properties_units_title($pInfo->properties_id|default:null, $lang['id']), ['id'=>'select-unit-'|cat:$lang['id'], 'class'=>'form-control', 'placeholder'=>TEXT_UNIT_DESCRIPTION, 'autocomplete'=>'off'])}</div>
                      </div>

                      <script type="text/javascript">
                          $(document).ready(function() {
                              $('#select-unit-{$lang['id']}').autocomplete({
                                  source: "{Yii::$app->urlManager->createUrl('properties/units')}",
                                  minLength: 0,
                                  autoFocus: true,
                                  delay: 0,
                                  appendTo: '.unit_group_{$lang['id']}',
                                  open: function (e, ui) {
                                      if ($(this).val().length > 0) {
                                          var acData = $(this).data('ui-autocomplete');
                                          acData.menu.element.find('a').each(function () {
                                              var me = $(this);
                                              var keywords = acData.term.split(' ').join('|');
                                              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                                          });
                                      }
                                  }
                              }).focus(function () {
                                  $(this).autocomplete("search");
                              });

                              $( ".ps_desc" ).sortable({
                                  axis: 'y',
                                  update: function( event, ui ) {
                                      i = 1;
                                      $(this).find('.js-sort-order').each(function() {
                                          $(this).val(i++);
                                      });
                                  },
                                  handle: ".handle"
                              }).disableSelection();


                          });
                      </script>


                    </div>

                  </div>
                  <div class="col-md-4 form-container border-left padding-b-2">

                    <h4>{$smarty.const.TEXT_ADDITIONAL_INFO}</h4>

                    <div class="row properties_descr">
                      <div class="col-md-4"><label>{$smarty.const.TEXT_ADDITIONAL_INFO}</label></div>
                      <div class="col-md-8">
                        <input type="checkbox" name="additional_info" value="1" class="check_on_off check_desc" {if {$app->controller->view->additional_info|default:null > 0}} checked="checked" {/if}>
                      </div>
                    </div>

                    <div class="row properties_descr use_desc"{if {$app->controller->view->additional_info|default:null > 0}} style="display:table-row;" {else} style="display:none;" {/if}>
                      <div class="col-md-4"><label>{$smarty.const.TEXT_PROPERTIES_DESCRIPTION}</label></div>
                      <div class="col-md-8">
                        <textarea name="properties_description[{$lang['id']}]" class="form-control">{\common\helpers\Properties::get_properties_description($pInfo->properties_id|default:null, $lang['id'])}</textarea>
                      </div>
                    </div>

                  </div>
                </div>


              </div>
            </div>


            <div class="widget box use_pos_values">
              <div class="widget-header">
                <h4>{$smarty.const.TEXT_PROPERTY}</h4>
                <div class="toolbar no-padding">
                  <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                  </div>
                </div>
              </div>
              <div class="widget-content">

                <div class="possible_values">
                  <div class="heading-row">
                    <div class="check-field"></div>
                    <div class="handle-head"></div>
                    <div class="name-field">{$smarty.const.TEXT_POSSIBLE_VALUES}</div>
                    <div class="alternative-name-field">{$smarty.const.TEXT_ALTERNATIVE_CONST}</div>
                    <div class="seo-field">{$smarty.const.TEXT_SEO_PAGE_NAME}</div>
                    <div class="image-map-field">Map</div>
                    <div class="prefix-field">Prefix</div>
                    <div class="postfix-field">Postfix</div>
                    <div class="color-field">{$smarty.const.TEXT_COLOR_}</div>
                    <div class="icon-field">{$smarty.const.TEXT_ICON}</div>
                    <div class="remove-field"></div>
                  </div>
                  <div class="ps_desc">
                      {if isset($app->controller->view->properties_values[$lang['id']]) && $app->controller->view->properties_values[$lang['id']]|@count > 0}
                          {$num = 0}
                          {*foreach $app->controller->view->properties_values_sorted_ids as $val_id => $val_id*}
                          {foreach $app->controller->view->properties_values[$lang['id']] as $val_id => $values}
                              {$num = $num + 1}
                              {include file="prop_value.tpl" val_id=$val_id lang_id=$lang['id'] value=$app->controller->view->properties_values[$lang['id']][$val_id] is_default_lang=($lang['code']==$default_language) pInfo=$pInfo}
                          {/foreach}
                      {/if}
                    <div align="right" class="ps_button_{$lang['id']}"><a class="btn btn-add">{$smarty.const.TEXT_ADD_MORE}</a></div>
                  </div>
                  <div class="ps_desc_template_{$lang['id']}" style="display:none;">
                      {include file="prop_value.tpl" val_id='__val_id__' lang_id=$lang['id'] value=array() is_default_lang=($lang['code']==$default_language) pInfo=$pInfo}</div>
                </div>

              </div>
            </div>


          </div>
        {/foreach}
      </div>
      </div>
    </div>
    <div class="buttons after">
    {if !$app->controller->view->usePopupMode}
      <a href="{Yii::$app->urlManager->createUrl('properties/index')}?parID={$pInfo->parent_id}&pID={$pInfo->properties_id|default:null}" class="btn btn-back">
          {$smarty.const.IMAGE_BACK}
      </a>
      <span class="btn btn-cancel" onclick="window.location.reload()">
          {$smarty.const.IMAGE_CANCEL}
      </span>
    {/if}
      <button class="btn btn-save">{$smarty.const.IMAGE_SAVE}</button>
    </div>
  </form>
</div>
<script type="text/javascript">
<!--
function hideTabs() {
    $('.lang-tabs a:first').trigger('click');
    $('.lang-tabs').hide();
    $('.lang-tabs + .tab-content').css({
        border: 'none',
        padding: '0',
        background: '#fff'
    })
}
function showTabs() {
    $('.lang-tabs').show();
    $('.lang-tabs + .tab-content').css({
        border: '',
        padding: '',
        background: ''
    })
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
        hideTabs();
    } else if ((var1.target.className == 'check_on_off same_all') && var2 == false) {
        showTabs();
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

function delPropValue(val_id) {
  $('.prop_value_' + val_id).remove();
}

$.fn.uploads2 = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  var body = $('body');
  var html = $('html');

  return this.each(function() {
    var _this = $(this);
    if (_this.data('value')) {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">{$smarty.const.TEXT_DRAG_DROP|escape:'html'}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD|escape:'html'}</span></div>\
      <div class="upload-file dz-clickable dz-started"><div class="dz-details dz-processing dz-success dz-image-preview"><div class="dz-filename"><span data-dz-name="">' + _this.data('value') + '</span></div><div class="upload-remove"></div></div></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
      $('.upload-remove', _this).click(function(){
        $('.upload-file', _this).html('');
        _this.removeAttr('data-value');
        $('input[name="' + _this.data('name').replace('upload_docs', 'values') + '"]').val('');
      })
    } else {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">{$smarty.const.TEXT_DRAG_DROP|escape:'html'}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD|escape:'html'}</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
    }

    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
        })
      },
      previewTemplate: '<div class="dz-details"><div class="dz-filename"><span data-dz-name=""></span></div><div class="upload-remove"></div></div>',
      dataType: 'json',
      drop: function(){
        $('.upload-file', _this).html('');
      }
    });

  })
};

var new_val_counter = 0;
$('.btn-add').click(function() {
  new_val_counter++;
  try {
    so = parseInt($('.js-sort-order:visible:last').val());
    if (isNaN(so)) {
      so = 0;
    }
  } catch (e){ so = 0; }
  so++;

  {foreach $languages as $lang}
  $('.ps_button_{$lang['id']}').before($('.ps_desc_template_{$lang['id']}').html().replace(/__val_id__/g, 'new' + new_val_counter));
  $('#so_new' + new_val_counter + '_{$lang['id']}').val(so);
  {/foreach}
  $('.upload_doc', $('.prop_value_new' + new_val_counter)).uploads2();
  return false;
})
;

function changePropertyFilter() {
  if ($('select[name=filter_by_property]').val() == 0) {
    $('#div_property_filter_steps').hide();
  } else {
    $('#div_property_filter_steps').show();
    if (!$('#filterSteps').hasClass('check_on_off')) {
      $('#filterSteps').bootstrapSwitch({
        onText: "|",
        offText: "O",
        handleWidth: '38px',
        labelWidth: '24px'
        }
      );
      $('#filterSteps').addClass('check_on_off');
    }
  }
};

function changePropertyType() {
  if ($('select[name=properties_type]').val() == 'number' || $('select[name=properties_type]').val() == 'interval') {
    $('.property_option').show();
    $('.property_format').show();
    $('select[name=multi_line]').hide();
    $('select[name=decimals]').show();
    $('.use_pos_values').show();
    $('.upload_doc').hide();
  } else if ($('select[name=properties_type]').val() == 'text') {
    $('.property_option').show();
    $('.property_format').show();
    $('select[name=multi_line]').show();
    $('select[name=decimals]').hide();
    $('.use_pos_values').show();
    $('.upload_doc').hide();
  } else if ($('select[name=properties_type]').val() == 'file') {
    $('.property_option').hide();
    $('.property_format').hide();
    $('.use_pos_values').show();
    $('.upload_doc').show();
  } else {
    $('.property_option').hide();
    $('.property_format').hide();
    $('.use_pos_values').hide();
    $('.upload_doc').hide();
  }
  if ($('select[name=properties_type]').val() == 'interval') {
    $('.show-interval').show();
  } else {
    $('.show-interval').hide();
  }
  if ($('select[name=properties_type]').val() == 'text' && $('select[name=multi_line]').val() > 0) {
    $('.can-be-textarea').each(function () {
      textbox =   $(document.createElement('textarea')).
                    attr('name', $(this).attr('name')).
                    attr('class', $(this).attr('class')).
                    attr('onchange', $(this).attr('onchange')).
                    attr('placeholder', $(this).attr('placeholder')).
                    html($(this).val() ? $(this).val() : $(this).html());
      $(this).replaceWith(textbox);
    });
  } else {
    $('.can-be-textarea').each(function () {
      inputbox =  $(document.createElement('input')).attr('type', 'text').
                    attr('name', $(this).attr('name')).
                    attr('class', $(this).attr('class')).
                    attr('onchange', $(this).attr('onchange')).
                    attr('placeholder', $(this).attr('placeholder')).
                    val($(this).val() ? $(this).val() : $(this).html());
      $(this).replaceWith(inputbox);
    });
    if ($('select[name=properties_type]').val() == 'file') {
      $('.div-interval .can-be-textarea').hide();
    }
  }
}
changePropertyType();

function changeDefaultLang(theInput, default_lang) {
  $('input[name^="' + theInput.name.replace('[' + default_lang + ']', '[') + '"]').each(function(index) {
    $(this).attr('placeholder', theInput.value);
  });
}

$('.gallery-filedrop-container').each(function() {

  var $filedrop = $(this);

  function createImage (file, $container) {
    var $preview = $('.gallery-template', $filedrop);
    $image = $('img', $preview);
    var reader = new FileReader();
    $image.height(48);
    reader.onload = function(e) {
        $image.attr('src',e.target.result);
    };
    reader.readAsDataURL(file);
    $preview.appendTo($('.gallery-filedrop-queue', $container));
    $.data(file, $preview);
  }

  $(function () {

    $('.gallery-filedrop-fallback-trigger', $filedrop)
      .on('click', function(e) {
        e.preventDefault();
        $('#' + $('.elgg-input-file', $filedrop).attr('id')).trigger('click');
      })

    $filedrop.filedrop({
      fallback_id : $('.elgg-input-file', $filedrop).attr('id'),
      url: "{Yii::$app->urlManager->createUrl('upload/index')}",
      paramname: 'file',
      maxFiles: 1,
      maxfilesize : 20,
      allowedfiletypes: ['image/jpeg','image/png','image/gif'],
      allowedfileextensions: ['.jpg','.jpeg','.png','.gif'],
      error: function(err, file) {
        console.log(err);
      },
      uploadStarted: function(i, file, len) {
        $('.properties_image', $filedrop).hide();
        createImage(file, $filedrop);
      },
      progressUpdated: function(i, file, progress) {
        $.data(file).find('.gallery-filedrop-progress').width(progress);
      },
      uploadFinished: function (i, file, response, time) {
        $('.elgg-input-hidden', $filedrop).val(file.name);
      }
    });
  });

});

$('.upload_doc').uploads2();

{if {$app->controller->view->usePopupMode && $pInfo->properties_id|default:null > 0}}
$('.properties_top').hide();
$('.properties_descr').hide();
{/if}
//-->
</script>
