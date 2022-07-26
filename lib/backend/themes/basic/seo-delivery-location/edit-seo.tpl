{use class="yii\helpers\Html"}
<div>
    {if count($languages) > 1}
        <ul class="nav nav-tabs under_tabs_ul">
            {foreach $languages as $lang}
                <li{if $lang['code'] == $default_language} class="active"{/if}><a href="#seo_tab_{$lang['code']}" data-toggle="tab">{$lang['logo']}<span>{$lang['name']}</span></a></li>
            {/foreach}
        </ul>
    {/if}
    <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
        {foreach $languages as $lang}
            <div class="tab-pane{if $lang['code'] == $default_language} active{/if}" id="seo_tab_{$lang['code']}">
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_PAGE_META_TITLE}:</label>
                    {Html::textInput('meta_title['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['meta_title'], ['class'=>'form-control form-control-small meta_title'] )}
                    {Html::checkbox('overwrite_head_title_tag['|cat:$lang['id']|cat:']', !!$location_data['text'][$lang['id']]['overwrite_head_title_tag'] && !$location_data['create'], ['class'=>'', 'value'=>'1'] )} {$smarty.const.TEXT_OVERWRITE_PAGE_TITLE}
                </div>

                <div class="edp-line">
                    <label>{$smarty.const.TEXT_PAGE_META_DESCRIPTION}:</label>
                    {Html::textInput('meta_description['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['meta_description'], ['class'=>'form-control form-control-small meta_description'] )}
                    {Html::checkbox('overwrite_head_desc_tag['|cat:$lang['id']|cat:']', !!$location_data['text'][$lang['id']]['overwrite_head_desc_tag'] && !$location_data['create'], ['class'=>'', 'value'=>'1'] )} {$smarty.const.TEXT_OVERWRITE_HEADER_DESCRIPTION}
                </div>

                <div class="edp-line">
                    <label>{$smarty.const.TEXT_SEO_PAGE_NAME}</label>
                    {Html::textInput('seo_page_name['|cat:$lang['id']|cat:']', $location_data['text'][$lang['id']]['seo_page_name'], ['class'=>'form-control'] )}
                </div>
            </div>
        {/foreach}
    </div>
    <div>&nbsp;</div>

    <div class="edp-line">
        <label>{$smarty.const.TEXT_OLD_SEO_URL}</label>
        <span class="js-old_seo_links">{Html::textInput('old_seo_page_name', $location_data['old_seo_page_name'], ['id'=>'txtOldSeoUrl', 'class'=>'form-control seo-input-field'])}
            <a href="#" data-base-href="{$catalogBaseHref}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
            {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$catalogBaseHref}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
            {/if}
        </span>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('.meta_title').limitValue('title');
        $('.meta_description').limitValue('description');

        $('body').on('click', ".js-old_seo_links .icon-home", function(){
            $(this).attr('href', $(this).attr('data-base-href')+$('#txtOldSeoUrl').val());
        });
        $('body').on('click', '.js-old_seo_links .icon-external-link', function(){
            $(this).attr('href', $(this).attr('data-base-href')+$('#txtOldSeoUrl').val());
        });
    })
</script>