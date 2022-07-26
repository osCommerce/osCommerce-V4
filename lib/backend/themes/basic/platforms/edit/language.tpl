{use class="yii\helpers\Html"}
<div class="w-line-row langs-block">
    <table class="tl-grid countries-table">
        <tr>
            <th>{$smarty.const.BOX_LOCALIZATION_LANGUAGES}</th>
            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
            <th>{$smarty.const.TEXT_DEFAULT}</th>
        </tr>
        {foreach $languages as $_lang1}
            <tr class="act {if in_array($_lang1['code'], $platform_languages)}active_row{else}hide_row{/if} lang_{$_lang1['code']}">
                <td>{$_lang1['image']} <span>{$_lang1['name']}</span></td>
                <td class="lang_status"><span class="check"></span></td>
                <td class="default_check">{if $pInfo->default_language|default:null == $_lang1['code']}<span class="check"></span>{/if}</td>
            </tr>
        {foreachelse}
            <tr><td>{$smarty.const.TEXT_NOT_CHOOSEN}</td><td></td><td></td></tr>
        {/foreach}
    </table>
    <div class="countries_popup popup-box-wrap-page hide_popup" id="countries-table">
        <div class="around-pop-up-page"></div>
        <div class="popup-box-page">
            <div class="pop-up-close-page"></div>
            <div class="pop-up-content-page">
                <div class="popup-heading">{$smarty.const.TEXT_SET_UP} {$smarty.const.BOX_LOCALIZATION_LANGUAGES}</div>
                <div class="popup-content"><table class="tl-grid countries-table">
                        <tr>
                            <th>{$smarty.const.BOX_LOCALIZATION_LANGUAGES}</th>
                            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                            <th>{$smarty.const.TEXT_DEFAULT}</th>
                        </tr>
                        {foreach $languages as $_lang}
                            <tr class="act popup_lang_{$_lang['code']}">
                                <td>{$_lang['image']} <span>{$_lang['name']}</span></td>
                                <td>{Html::checkbox('planguages[]', in_array($_lang['code'], $platform_languages) , ['value' => $_lang['code'], 'class' => 'p_languages']) }</td>
                                <td>{Html::radio('default_language', ($pInfo->default_language|default:null == $_lang['code']), ['value'=> $_lang['code'], 'class' => 'd_languages', 'title' => {$smarty.const.TEXT_DEFAULT}])}</td>
                            </tr>
                        {foreachelse}
                            <tr><td>{$smarty.const.TEXT_NOT_CHOOSEN}</td><td></td><td></td></tr>
                        {/foreach}
                    </table>
                    <div class="btn-bar">
                        <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                        <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="btn-small-bar after">
        <div class="btn-right"><a class="btn popup_lang" href="#countries-table" data-class="countries-table">{$smarty.const.BUTTON_ADD_MORE_NEW}</a></div>
    </div>
</div>