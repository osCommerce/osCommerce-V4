{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TABLE_TEXT_NAME}
    </div>
    <div class="popup-content">




        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TABLE_TEXT_NAME}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">




                    <h4>{$smarty.const.SETTINGS_FOR_EDIT_LINK}</h4>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_CHOOSE_PAGE}</label>
                        <select name="setting[0][link]" id="" class="form-control">
                            <option value=""{if $settings[0].link == ''} selected{/if}>{$smarty.const.TEXT_DASHBOARD}</option>
                            <option value="logoff"{if $settings[0].link == 'logoff'} selected{/if}>{$smarty.const.HEADER_TITLE_LOGOFF}</option>
                            {foreach $links as $link}
                                <option value="{$link}"{if $settings[0].link == $link} selected{/if}>{$link}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LINK_TEXT}</label>
                        <input type="text" name="setting[0][text]" value="{$settings[0].text}" id="" class="form-control" style="width: 243px">
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIKE_BUTTON}</label>
                        <select name="setting[0][like_button]" id="" class="form-control">
                            <option value=""{if $settings[0].like_button == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].like_button == '1'} selected{/if}>{$smarty.const.HEADING_TYPE} 1</option>
                            <option value="2"{if $settings[0].like_button == '2'} selected{/if}>{$smarty.const.HEADING_TYPE} 2</option>
                            <option value="3"{if $settings[0].like_button == '3'} selected{/if}>{$smarty.const.HEADING_TYPE} 3</option>
                            <option value="4"{if $settings[0].like_button == '4'} selected{/if}>{$smarty.const.HEADING_TYPE} 4</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_OPEN_IN_POPUP}</label>
                        <input type="checkbox" name="setting[0][popup]"{if $settings[0].popup} checked{/if}>
                    </div>


                    <h4>Settings for add more button</h4>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_CHOOSE_PAGE}</label>
                        <select name="setting[0][link_add]" id="" class="form-control">
                            <option value=""{if $settings[0].link_add == ''} selected{/if}>{$smarty.const.TEXT_DASHBOARD}</option>
                            <option value="logoff"{if $settings[0].link_add == 'logoff'} selected{/if}>{$smarty.const.HEADER_TITLE_LOGOFF}</option>
                            {foreach $links as $link}
                                <option value="{$link}"{if $settings[0].link_add == $link} selected{/if}>{$link}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LINK_TEXT}</label>
                        <input type="text" name="setting[0][text_add]" value="{$settings[0].text_add}" id="" class="form-control" style="width: 243px">
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIKE_BUTTON}</label>
                        <select name="setting[0][like_button_add]" id="" class="form-control">
                            <option value=""{if $settings[0].like_button_add == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].like_button_add == '1'} selected{/if}>{$smarty.const.HEADING_TYPE} 1</option>
                            <option value="2"{if $settings[0].like_button_add == '2'} selected{/if}>{$smarty.const.HEADING_TYPE} 2</option>
                            <option value="3"{if $settings[0].like_button_add == '3'} selected{/if}>{$smarty.const.HEADING_TYPE} 3</option>
                            <option value="4"{if $settings[0].like_button_add == '4'} selected{/if}>{$smarty.const.HEADING_TYPE} 4</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_OPEN_IN_POPUP}</label>
                        <input type="checkbox" name="setting[0][popup_add]"{if $settings[0].popup_add} checked{/if}>
                    </div>






                </div>
                <div class="tab-pane" id="style">
                    {include '../include/style.tpl'}
                </div>
                <div class="tab-pane" id="align">
                    {include '../include/align.tpl'}
                </div>
                <div class="tab-pane" id="visibility">
                    {include '../include/visibility.tpl'}
                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>