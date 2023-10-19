{use class="yii\helpers\Url"}
{use class="common\helpers\Html"}
<div class="language_edit">
{*
{if $languages_id}
  <div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_EDIT_LANGUAGE}</div>
{else}
  <div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_NEW_LANGUAGE}</div>
{/if}
*}
			  {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}

	  <form method="post" name="languages" action="{Url::to(['save', 'languages_id'=>$lang['languages_id'], 'action' => 'save'])}">
    <input type="hidden" name="row_id" value="{$row}">
    <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs top_tabs_ul main_tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#tab_main"><a><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
                <li class="" data-bs-toggle="tab" data-bs-target="#tab_formats"><a><span>Formats</span></a></li>
              </ul>
              <input type="hidden" name="tab" value="">
              <div class="tab-content">
                <div class="tab-pane topTabPane tabbable-custom active" id="tab_main">
                    
                      <div class="main_row">
                          <div class="main_title">
                      {if $lang['languages_status'] eq 1}
                        {$smarty.const.IMAGE_ICON_STATUS_GREEN_LIGHT}<span class="colon">:</span>&nbsp;&nbsp;{Html::checkbox('flag', true, ['class' => 'check_on_off'])}
                      {else}
                        {$smarty.const.IMAGE_ICON_STATUS_RED_LIGHT}<span class="colon">:</span>&nbsp;&nbsp;{Html::checkbox('flag', false, ['class' => 'check_on_off'])}
                      {/if}
                          </div>
                      {if strtolower(DEFAULT_LANGUAGE) neq strtolower($lang['code'])}
                          <div class="main_title">
                            {$smarty.const.TEXT_HIDE_IN_ADMIN}<span class="colon">:</span>&nbsp;&nbsp;{Html::checkbox('hide_in_admin', $lang['hide_in_admin'], ['value' => 1, 'class' => 'check_on_off'])}
                          </div>
                      {/if}
                       </div>
                    
                      <div class="main_row">
                        <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_NAME}</div>
                        <div class="main_value">{tep_draw_input_field('name', $lang['name'], 'class="form-control"')}</div>
                      </div>
                      <div class="main_row">
                        <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_CODE}</div>
                        <div class="main_value">{tep_draw_input_field('code', strtoupper($lang['code']), 'class="form-control"')}</div>
                      </div>
                      <div class="main_row images">
                        <!--<div class="block">
                          <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_IMAGE}&nbsp;(gif)</div>
                          <div class="main_value"><div class="upload" data-name="image" data-value="{if $lang['image'] neq ''}{$lang['image']} {/if}"></div>
                                                {$lang['image']}</div>
                        </div>  -->                    
                        <div class="block">
                          <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_IMAGE}&nbsp;(svg)</div>
                          <div class="main_value"><div class="upload" data-name="image_svg" data-value="{if $lang['image_svg'] neq ''}{$lang['image_svg']} {/if}"></div>
                                                {$lang['image_svg']}</div>                                
                         </div>
                        </div>
                       
                        <!--<div class="main_row">
                          <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_SORT_ORDER}</div>
                          <div class="main_value">{tep_draw_input_field('sort_order', $lang['sort_order'], 'class="form-control"')}</div>
                        </div>
                        <div class="main_row">
                          <div class="main_title">{$smarty.const.TEXT_INFO_LANGUAGE_DIRECTORY}</div>
                          <div class="main_value">{tep_draw_input_field('directory', $lang['directory'], 'class="form-control"')}</div>
                        </div>
                        -->
                    
                        <div class="main_row">
                          <div class="main_title">{$smarty.const.TEXT_INFO_LOCALE}</div>
                          <div class="main_value">{tep_draw_pull_down_menu('locale', $lList, $lang['locale'], 'class="form-control"')}</div>
                        </div>
                    
                    {if strtolower(DEFAULT_LANGUAGE) neq strtolower($lang['code'])}
                        <div class="main_row"><input type="checkbox" name="default" class="check_on_off">{$smarty.const.TEXT_SET_DEFAULT}</div>
                    {/if}
                </div>{*main*}
                <div class="tab-pane topTabPane tabbable-custom" id="tab_formats">
                      <div class="tabbable tabbable-custom">
                          <!--<ul class="nav nav-tabs top_tabs_ul">
                            {foreach $languages as $_l}
                            <li class="active" data-bs-toggle="tab" data-bs-target="#tab_lang_{$_l['code']}"><a><span>{$_l['image']}&nbsp;{$_l['name']}</span></a></li>
                            {/foreach}
                          </ul>-->
                          <div class="tab-inserted">
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_KEY}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_VALUE}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_DESCRIPTION}</div>
                                  </div>
                                  
                          {foreach $languages as $_l}
                            <div class="tab-pane topTabPane tabbable-custom active" id="tab_lang_{$_l['code']}">
                              {foreach $defined_formats[$_l['id']] as $key => $value}
                                {assign var='tmpKey' value="configuration_key[{$_l['id']}][]"}
                                {assign var='tmpValue' value="configuration_value[{$_l['id']}][]"}
                                {assign var='tmpDesc' value="configuration_description[{$_l['id']}][]"}
                                {assign var='vD' value="{$key}_DESC"}
                                  <div class="template_cell">
                                    <div class="main_value">{tep_draw_hidden_field({$tmpKey}, {$key}, 'class="form-control"')}{$key}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_value">{tep_draw_input_field({$tmpValue}, {$value}, 'class="form-control"')}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_value">{tep_draw_input_field({$tmpDesc}, {\common\helpers\Translation::getTranslationValue({$vD}, 'admin/languages', $_l['id'])}, 'class="form-control"')}</div>
                                    <!--<div class="upload-remove"></div>-->
                                  </div>
                                  
                                {/foreach}
                            </div>
                          {/foreach}
                          </div>
                        
                      <div class="holder">                        
                      </div>
                      <div class="main_row template" style="display:none">
                        <div class="tab-pane topTabPane tabbable-custom" id="tab_temp">
                          <div class="tabbable tabbable-custom">                      
                      
                            <ul class="nav nav-tabs top_tabs_ul">
                            {foreach $languages as $_l}
                              <li class="{if $_l['code'] == $smarty.const.DEFAULT_LANGUAGE}active{/if}" data-bs-toggle="tab" data-bs-target="#tab_temp_{$_l['code']}"><a><span>{$_l['image']}&nbsp;{$_l['name']}</span></a></li>
                            {/foreach}
                            </ul>
                             <div class="tab-content">
                              {foreach $languages as $_l}
                                <div class="tab-pane topTabPane tabbable-custom {if $_l['code'] == $smarty.const.DEFAULT_LANGUAGE}active{/if}" id="tab_temp_{$_l['code']}">
                                {assign var='tmpKey' value="configuration_key[{$_l['id']}][]"}
                                {assign var='tmpValue' value="configuration_value[{$_l['id']}][]"}
                                {assign var='tmpDesc' value="configuration_description[{$_l['id']}][]"}
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_KEY}</div>
                                    <div class="main_value">{tep_draw_input_field({$tmpKey}, '', 'class="regula form-control"')}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_VALUE}</div>
                                    <div class="main_value">{tep_draw_input_field({$tmpValue}, '', 'class="form-control"')}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_DESCRIPTION}</div>
                                    <div class="main_value">{tep_draw_input_field({$tmpDesc}, '', 'class="form-control"')}</div>
                                  </div>
                                  
                                </div>
                                {/foreach}
                              </div>
                              <br/>
                              
                             
                          </div>
                         </div>
                        </div>
                        <!--<input type="button" class="btn btn-insert" value="{$smarty.const.IMAGE_NEW}">-->
                </div>
      </div>{*content*}
    </div>{*tabbable*}
		<div class="btn-bar">
      <div class="btn-left">
      <a href="{Url::to(['index', 'row' => {$row}])}" class="btn btn-cancel" >{$smarty.const.IMAGE_CANCEL}</a>
      </div>
      <div class="btn-right">
      <input type="submit" value="{if $languages_id}{$smarty.const.IMAGE_UPDATE}{else}{$smarty.const.IMAGE_INSERT}{/if}" class="btn btn-no-margin btn-primary">      
      </div>
    </div>
		</form>
    <div class="upload-remove"></div>
    
    <script type="text/javascript">
      
  (function($){
  
    $('input[name=tab]').val('#tab_main');
    $('.main_tabs li a').click(function(){
      $('input[name=tab]').val($('.main_tabs li:not(.active) a').attr('href'));
    })    
  
    var newRow = true;
    $('body').on('change', '.regula', function(){
      var parent = $(this).parents('.standalone');
      $('.holder .regula:not(.currentTemp)').val($('.holder .currentTemp').val());
    }).on('focus', '.regula', function(){
      $('.holder .regula').removeClass('currentTemp');
      $(this).addClass('currentTemp');
    })
    
    $('.btn-insert').click(function(){
     if (newRow){
        $('.holder').append($('.template').clone().removeClass('template').addClass('standalone').show());
        newRow = false;
     }      
    })
  
	 $.fn.uploads2 = function(options){
    var option = jQuery.extend({
    overflow: false,
    box_class: false
    },options);

    var body = $('body');
    var html = $('html');

    return this.each(function() {

      var _this = $(this);

      _this.html('\
      <div class="upload-file-wrap">\
        <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES}<br>{$smarty.const.TEXT_OR}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD}</span></div>\
        <div class="upload-file" style="width:147px;height:147px;"></div>\
        <div class="upload-hidden"><input type="hidden" name="'+_this.data('name')+'" value="'+_this.data('value')+'"/></div>\
      </div>');
      if ( _this.data('value') ) {
        _this.append('<img src="{$smarty.const.DIR_WS_CATALOG_IMAGES}icons/'+_this.data('value')+'" style="max-width:26px;">');
      }


      $('.upload-file', _this).dropzone({
        url: "{Yii::$app->urlManager->createUrl('upload')}",
        sending:  function(e, data) {
          $('.upload-hidden input[type="hidden"]', _this).val(e.name);
          $('.upload-remove', _this).on('click', function(){
            $('.dz-details', _this).remove();
            $('input[type="hidden"]', _this).val(_this.data('value'))
          })
        },
        dataType: 'json',
        previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="upload-remove"></div></div>',
        drop: function(){
          $('.upload-file', _this).html('')
        }
      });

    })
  };  

        $(".check_on_off").bootstrapSwitch(
        {
          onText: "{$smarty.const.SW_ON}",
          offText: "{$smarty.const.SW_OFF}",
          handleWidth: '20px',
          labelWidth: '24px'
        });
  

  
  $('.upload').uploads2();

  })(jQuery)

      
    </script>
    