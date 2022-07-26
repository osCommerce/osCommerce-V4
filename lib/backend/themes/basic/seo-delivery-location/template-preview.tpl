{use class="yii\helpers\Html"}
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
                                <td class="label_name">{$smarty.const.TEXT_LOCATION_NAME}</td>
                                <td class="label_value">{$template_data[$lang['id']]['location_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_LOCATION_DESCRIPTION}</td>
                                <td class="label_value">{$template_data[$lang['id']]['location_description']}</td>
                            </tr>

                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_PAGE_META_TITLE}</td>
                                <td class="label_value">{$template_data[$lang['id']]['meta_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_PAGE_META_KEYWORDS}</td>
                                <td class="label_value">{$template_data[$lang['id']]['meta_keyword']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_PAGE_META_DESCRIPTION}</td>
                                <td class="label_value">{$template_data[$lang['id']]['meta_description']}</td>
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
    </div>

