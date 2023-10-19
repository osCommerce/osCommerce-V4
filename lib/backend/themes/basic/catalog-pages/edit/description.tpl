{if count($languages) > 1}
    <ul class="nav nav-tabs under_tabs_ul">
        {foreach $languages as $lang}
            <li{if $lang->code == $default_language} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lang->code}"><a>{$lang->svgSrc}<span>{$lang->name}</span></a></li>
        {/foreach}
    </ul>
{/if}
<div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $catalogPageForm->catalogPageDescriptionForm as $langId => $catalogPageDescriptionForm}
        <div class="tab-pane{if $languages[$langId]->code == $default_language} active{/if}" id="tab_{$languages[$langId]->code}">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td colspan="2">
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']name')}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']description_short')->textarea(['class'=>'ckeditor form-control'])}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']description')->textarea(['class'=>'ckeditor form-control'])}
                    </td>
                </tr>
            </table>

        </div>
    {/foreach}
</div>