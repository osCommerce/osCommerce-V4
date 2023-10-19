<style>
.blockH .categoryH:first-child .del-tag{
    display:none;
}
</style>
{if count($languages) > 1}
    <ul class="nav nav-tabs under_tabs_ul">
        {foreach $languages as $lang}
            <li{if $lang->code == $default_language} class="active"{/if} data-bs-toggle="tab" data-bs-target="#seo_tab_{$lang->code}"><a>{$lang->svgSrc}<span>{$lang->name}</span></a></li>
        {/foreach}
    </ul>
{/if}
<div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
    {foreach $catalogPageForm->catalogPageDescriptionForm as $langId => $catalogPageDescriptionForm}
        <div class="tab-pane{if $languages[$langId]->code == $default_language} active{/if}" id="seo_tab_{$languages[$langId]->code}">
            <table class="h-teg-table" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td>
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']meta_title')}
                    </td>
                </tr>
                <tr>
                    <td>
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']meta_description')}
                    </td>
                </tr>
                <tr>
                    <td>
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']meta_keyword')}
                    </td>
                </tr>
                <tr>
                    <td>
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']slug')}
                    </td>
                </tr>
                <tr>
                    <td>
                        {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']h1_tag')}
                    </td>
                </tr>
                <tr>
                    <td class="blockH">
                        <div class="wrapH">
                            {foreach $catalogPageDescriptionForm.h2_tag as $key => $h2}
                                <span class="categoryH">
                                        <span class="row">
                                            {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']h2_tag['|cat:$key|cat:']',['template' => '{label}{input}<span class="del-pt del-tag"></span>{error}{hint}'])->textInput(['value'=>$h2])}
                                        </span>
                                </span>
                            {/foreach}
                        </div>
                        <span class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
                    </td>
                </tr>
                <tr>
                    <td class="blockH">
                        <div class="wrapH">
                            {foreach $catalogPageDescriptionForm.h3_tag as $key => $h3}
                                <span class="categoryH">
                                        <span class="row">
                                            {$form->field($catalogPageDescriptionForm, '['|cat:$langId|cat:']h3_tag['|cat:$key|cat:']',['template' => '{label}{input}<span class="del-pt del-tag"></span>{error}{hint}'])->textInput(['value'=>$h3])}
                                        </span>
                                    </span>
                            {/foreach}
                        </div>
                        <span class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span>
                    </td>
                </tr>
            </table>

        </div>
    {/foreach}
</div>
<script>
    var bodySelector = $('body');
    bodySelector.on('click','.del-tag',function () {
        $(this).closest('.categoryH').remove();
    })
    bodySelector.on('click','.btn-add-more',function () {
        var parentSelector =  $(this).closest('.blockH').find('.wrapH');
        var addSelector = parentSelector.find('.categoryH:first').first().clone();
        addSelector.appendTo(parentSelector);
        $('input',parentSelector).each(function( index ) {
            var name = $(this).attr('name');
            name = name.replace(/\[(\w+)\]$/, '['+index+']');
            $(this).attr('name',name);
        });
        var cloneInput = $('input',parentSelector).last();
        cloneInput.val('');
    });
</script>