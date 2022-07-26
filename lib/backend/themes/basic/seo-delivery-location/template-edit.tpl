{use class="yii\helpers\Html"}
<form action="" id="frmLocationTemplate">
    <input type="hidden" name="platform_id" value="{$platform_id}"/>
    <input type="hidden" name="parent_id" value="{$parent_id}"/>
    <div class="popup-heading">{$page_name}</div>
    <div class="popup-content pop-mess-cont">
        <p>
        <div class="tabbable tabbable-custom">
            {if count($languages) > 1}
        <ul class="nav nav-tabs under_tabs_ul">
            {foreach $languages as $lang}
                <li{if $lang['id'] == $active_language_id} class="active"{/if}><a href="#template_tab_{$lang['code']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>
            {/foreach}
        </ul>
        {/if}
        <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
            {foreach $languages as $lang}
                <div class="tab-pane{if $lang['id'] == $active_language_id} active{/if}" id="template_tab_{$lang['code']}">
                    <table cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_LOCATION_NAME}{if $level>0 && !$template_data[$lang['id']]['location_name']}<div class="template_use_parent">{$smarty.const.TEXT_USE_PARENT_LEVEL_DATA}</div>{/if}</td>
                            <td class="label_value">{Html::textInput('location_name['|cat:$lang['id']|cat:']', $template_data[$lang['id']]['location_name'], ['class'=>'form-control'] )}</td>
                        </tr>
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_LOCATION_DESCRIPTION}{if $level>0 && !$template_data[$lang['id']]['location_description']}<div class="template_use_parent">{$smarty.const.TEXT_USE_PARENT_LEVEL_DATA}</div>{/if}</td>
                            <td class="label_value">{Html::textarea('template_location_description['|cat:$lang['id']|cat:']', $template_data[$lang['id']]['location_description'], ['class'=>'form-control popupCkeditor'] )}</td>
                        </tr>
                        
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_PAGE_META_TITLE}{if $level>0 && !$template_data[$lang['id']]['meta_title']}<div class="template_use_parent">{$smarty.const.TEXT_USE_PARENT_LEVEL_DATA}</div>{/if}</td>
                            <td class="label_value">
                                <div class="row">
                                    <div class="col-md-8">
                                    {Html::textInput('meta_title['|cat:$lang['id']|cat:']', $template_data[$lang['id']]['meta_title'], ['class'=>'form-control'] )}
                                    </div>
                                    <div class="col-md-4">
                                        {Html::checkbox('overwrite_head_title_tag['|cat:$lang['id']|cat:']', !!$template_data[$lang['id']]['overwrite_head_title_tag'], ['class'=>'', 'value'=>'1'] )} {$smarty.const.TEXT_OVERWRITE_PAGE_TITLE}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label_name">{$smarty.const.TEXT_PAGE_META_DESCRIPTION}{if $level>0 && !$template_data[$lang['id']]['meta_description']}<div class="template_use_parent">{$smarty.const.TEXT_USE_PARENT_LEVEL_DATA}</div>{/if}</td>
                            <td class="label_value">
                                <div class="row">
                                    <div class="col-md-8">
                                {Html::textInput('meta_description['|cat:$lang['id']|cat:']', $template_data[$lang['id']]['meta_description'], ['class'=>'form-control'] )}
                                    </div>
                                    <div class="col-md-4">
                                {Html::checkbox('overwrite_head_desc_tag['|cat:$lang['id']|cat:']', !!$template_data[$lang['id']]['overwrite_head_desc_tag'], ['class'=>'', 'value'=>'1'] )} {$smarty.const.TEXT_OVERWRITE_HEADER_DESCRIPTION}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                </div>
            {/foreach}
        </div>
        </div>
        </p>
    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
    </div>
</form>
<script type="text/javascript">
    (function($){
        $(function(){
            $('#frmLocationTemplate').on('submit', function(){
                if (typeof(CKEDITOR) == 'object'){
                    for ( var instance in CKEDITOR.instances ) {
                        if ( CKEDITOR.instances.hasOwnProperty(instance) && instance.indexOf('template_')===0 ) {
                            CKEDITOR.instances[instance].updateElement();
                        }
                    }
                }
                var values = $(this).serializeArray();
                $.post('{$action}', values, function(){
                    $('.popup-box-wrap:last').remove();
                }, 'json');

                return false
            });
            CKEDITOR.replaceAll(function( textarea, config ) {
                if ( textarea.className && textarea.className.indexOf('popupCkeditor')!==-1 ) {
                    config.toolbarCanCollapse = true;
                    config.toolbarStartupExpanded = false;
                    config.height = 150;
                    return true;
                }else{
                    return false;
                }
            });
        });
    })(jQuery)
</script>