{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_VIDEO}
    </div>
    <div class="popup-content box-img">
        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TEXT_VIDEO}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">
                    <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs">
                            {foreach $languages as $language}
                                <li{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a>{$language.video} {$language.name}</a></li>
                            {/foreach}
                        </ul>
                        <div class="tab-content">
                            {foreach $languages as $language}
                                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">
                                    <h3><b>{$smarty.const.TEXT_VIDEO}</b></h3>
                                    <div>
                                        <textarea name="setting[{$language.id}][youtube_video]" cols="30" rows="2" placeholder="{$smarty.const.PLACE_HERE_CODE}" class="form-control">{$settings[$language.id].youtube_video}</textarea>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                    <div class="">
                        <h3><b>{$smarty.const.TEXT_SETTINGS}</b></h3>
                        <div class="setting-row">
                            <label for="">{$smarty.const.TEXT_WIDTH}</label>
                            <input type="number" name="setting[0][width_v]" value="{$settings[0].width_v}" class="form-control" /><span class="px">px</span>
                        </div>
                        <div class="setting-row">
                            <label for="">{$smarty.const.TEXT_HEIGHT}</label>
                            <input type="number" name="setting[0][height_v]" value="{$settings[0].height_v}" class="form-control" /><span class="px">px</span>
                        </div>
                        <p><label><input type="checkbox" name="setting[0][rel]"{if $settings[0].rel} checked{/if}/> {$smarty.const.TEXT_RELATED_VIDEO}</label></p>
                        <p><label><input type="checkbox" name="setting[0][controls]"{if $settings[0].controls} checked{/if}/> {$smarty.const.TEXT_CONTROL_PANEL}</label></p>
                        <p><label><input type="checkbox" name="setting[0][showinfo]"{if $settings[0].showinfo} checked{/if}/> {$smarty.const.TEXT_PLAYER_FUNCTION}</label></p>
                        <p><label><input type="checkbox" name="setting[0][showpopup]"{if $settings[0].showpopup} checked{/if}/> {$smarty.const.TEXT_SHOW_VIDEO_IN_POPUP}</label></p>
                    </div>
                    {include 'include/ajax.tpl'}
                </div>
                <div class="tab-pane" id="style">
                    {include 'include/style.tpl'}
                </div>
                <div class="tab-pane" id="align">
                    {include 'include/align.tpl'}
                </div>
                <div class="tab-pane" id="visibility">
                    {include 'include/visibility.tpl'}
                </div>
            </div>
        </div>
    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>