{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_NOTICE_SETTINGS}
    </div>
    <div class="popup-content box-img">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active"><a href="#text" data-toggle="tab">{$smarty.const.TEXT_NOTICE_SETTINGS}</a></li>
                <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
                <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="text">


                    <div class="setting-row">
                        <label for="">{$smarty.const.SHOW_NOTICE_IN}</label>
                        <select name="setting[0][position]" id="" class="form-control">
                            <option value=""{if $settings[0].position == ''} selected{/if}>{$smarty.const.TEXT_TOP_BAR}</option>
                            <option value="bottom"{if $settings[0].position == 'bottom'} selected{/if}>{$smarty.const.TEXT_BOTTOM_BAR}</option>
                            <option value="popup"{if $settings[0].position == 'popup'} selected{/if}>{$smarty.const.TEXT_POP_UP}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SHOW_CANCEL_BUTTON}</label>
                        <select name="setting[0][cancel_button]" id="" class="form-control">
                            <option value=""{if $settings[0].cancel_button == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].cancel_button == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_EXPIRES_DAYS}</label>
                        <input type="text" name="setting[0][expires_days]" class="form-control" value="{$settings[0].expires_days}"/>
                    </div>
                    <div class="setting-row">
                        <a href="{$app->urlManager->createUrl(['texts/edit', 'translation_key' => 'TEXT_COOKIE_NOTICE', 'translation_entity' => 'main'])}" target="_blank">{$smarty.const.TEXT_CHANGE_MESSAGE_TEXT}</a>, &nbsp;  &nbsp;
                        <a href="{$app->urlManager->createUrl(['texts/edit', 'translation_key' => 'TEXT_COOKIE_BUTTON', 'translation_entity' => 'main'])}" target="_blank">{$smarty.const.TEXT_CHANGE_BUTTON_TEXT}</a>
                    </div>



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