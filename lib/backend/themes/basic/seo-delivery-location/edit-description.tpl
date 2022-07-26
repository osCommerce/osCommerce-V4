{use class="yii\helpers\Html"}
{if count($languages) > 1}
    <ul class="nav nav-tabs under_tabs_ul">
        {foreach $languages as $lang}
            <li{if $lang['code'] == $default_language} class="active"{/if}><a href="#tab_{$lang['code']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>
        {/foreach}
    </ul>
{/if}
<div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $languages as $lang}
        <div class="tab-pane{if $lang['code'] == $default_language} active{/if}" id="tab_{$lang['code']}">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="label_name">{$smarty.const.TEXT_LOCATION_NAME}:<br>
                        <a href="javascript:void(0);" data-params="&language_id={$lang['id']}" class="js-template-edit">Edit template</a><br>
                        <a href="javascript:void(0);" data-params="&language_id={$lang['id']}" class="js-template-preview">Preview</a></td>
                    <td class="label_value">{Html::textInput('location_name['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['location_name'], ['class'=>'form-control'] )}</td>
                </tr>
                <tr>
                    <td class="label_name">{$smarty.const.TEXT_LOCATION_DESCRIPTION}</td>
                    <td class="label_value">{Html::textarea('location_description['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['location_description'], ['class'=>'ckeditor form-control'] )}</td>
                </tr>
                <tr>
                    <td class="label_name">{$smarty.const.TEXT_LOCATION_DESCRIPTION_SHORT}</td>
                    <td class="label_value">{Html::textarea('location_description_short['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['location_description_short'], ['class'=>'ckeditor form-control'] )}</td>
                </tr>
                <tr>
                    <td class="label_name">{$smarty.const.TEXT_LOCATION_DESCRIPTION_LONG}</td>
                    <td class="label_value">{Html::textarea('location_description_long['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['location_description_long'], ['class'=>'ckeditor form-control'] )}</td>
                </tr>
            </table>

        </div>
    {/foreach}
</div>