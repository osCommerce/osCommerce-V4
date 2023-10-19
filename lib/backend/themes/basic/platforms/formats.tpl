{use class="yii\helpers\Url"}
{use class="yii\helpers\Html"}
<div class="language_edit platform">

<form method="post" name="formats" action="{Url::to(['define-formats', 'id' => $platform_id, 'no_redirect'=>$no_redirect ])}"><input type="hidden" name="id" value="{$platform_id}">
    
    <div class="tabbable tabbable-custom">

                <div class="tab-pane topTabPane tabbable-custom" id="tab_formats">
                      <div class="tabbable tabbable-custom">
                          <ul class="nav nav-tabs top_tabs_ul">
                            {assign var=i value=0}
                            {foreach $languages as $_l}
                            <li class="{if $i == 0}active{/if}" data-bs-toggle="tab" data-bs-target="#tab_lang_{$_l['code']}"><a><span>{$_l['image']}&nbsp;{$_l['name']}</span></a></li>
                            {$i = $i+1}
                            {/foreach}
                          </ul>
                          <div class="tab-content tab-inserted">
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_KEY}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_VALUE}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_PLATFORM_VALUE}</div>
                                  </div>                                  
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_DESCRIPTION}</div>
                                  </div>
                          {assign var=i value=0}
                          {foreach $languages as $_l}
                            <div class="tab-pane topTabPane tabbable-custom {if $i == 0}active{/if}" id="tab_lang_{$_l['code']}">
                             {$i = $i+1}
                              {foreach $defined_formats[$_l['id']] as $key => $value}
                                {assign var='tmpKey' value="configuration_key[{$_l['id']}][]"}
                                {assign var='tmpValue' value="configuration_value[{$_l['id']}][]"}
                                {assign var='vD' value="{$key}_DESC"}
                                  <div class="template_cell">
                                    <div class="main_value">{tep_draw_hidden_field({$tmpKey}, {$key}, 'class="form-control"')}{$key}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_value">{$value}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_value">{tep_draw_input_field({$tmpValue}, $platform_formats[$_l['id']][{$key}], 'class="form-control"')}</div>
                                  </div>                                  
                                  <div class="template_cell">
                                    <div class="main_value">{\common\helpers\Translation::getTranslationValue({$vD}, 'admin/languages', $_l['id'])}</div>
                                  </div>
                                  
                                {/foreach}
                            </div>
                          {/foreach}
                          </div>                        
                </div>
      </div>{*content*}
    </div>{*tabbable*}
		<div class="btn-toolbar btn-toolbar-order"><input type="submit" value="{$smarty.const.IMAGE_SAVE}" class="btn btn-no-margin btn-primary"><a href="{Url::to(['index'])}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
		</form>
    
    </div>
    
<script>    
 $(document).ready(function(){
    
    $('form[name=formats]').submit(function(){
      var action = $(this).attr('action');
      $.post(action, 
          $('form[name=formats]').serialize(), 
          function (data, status){
            if (status == 'success'){
               $('.pop-up-close:last').trigger('click');
               {if !$no_redirect}
               $('#platforms_management_data').html(data);
               {/if}
            } else {
              alert('XMLHttpRequest error');
            }
          },
          'html'
      )
      return false;
    });
    
 })
</script>