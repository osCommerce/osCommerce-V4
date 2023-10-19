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




                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_USE_SWITCHER}</label>
                        <select name="setting[0][use_switcher]" id="" class="form-control">
                            <option value=""{if $settings[0].use_switcher == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].use_switcher == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_HIDE_PAGE}</label>
                        <select name="setting[0][hide_page]" id="" class="form-control">
                            <option value=""{if $settings[0].hide_page == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].hide_page == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_HIDE_CONTINUE_BUTTON}</label>
                        <select name="setting[0][hide_continue_button]" id="" class="form-control">
                            <option value=""{if $settings[0].hide_continue_button == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].hide_continue_button == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
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