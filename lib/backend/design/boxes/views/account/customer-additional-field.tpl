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
                        <label for="">Choose field</label>
                        <select name="setting[0][field]" id="" class="form-control">
                            <option value=""{if $settings[0].field == ''} selected{/if}></option>
                            {foreach $fieldsByGroup as $groupTitle => $fields}
                                <optgroup label="{$groupTitle}">
                                    {foreach $fields as $id => $title}
                                        <option value="{$id}"{if $settings[0].field == $id} selected{/if}>{$title}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">Show label</label>
                        <select name="setting[0][no_label]" id="" class="form-control">
                            <option value=""{if $settings[0].no_label == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].no_label == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">Style</label>
                        <select name="setting[0][style_view]" id="" class="form-control">
                            <option value=""{if $settings[0].style_view == ''} selected{/if}>Defalut</option>
                            <option value="table"{if $settings[0].style_view == 'table'} selected{/if}>Characters in table</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">Cells (for characters in table)</label>
                        <input name="setting[0][cells]" value="{$settings[0].cells}" class="form-control">
                    </div>

                    <div class="setting-row">
                        <label for="">Default value from field</label>
                        <select name="setting[0][default_fields_id]" id="" class="form-control">
                            <option value=""{if $settings[0].default_fields_id == ''} selected{/if}></option>
                            {foreach $fieldsByGroup as $groupTitle => $fields}
                                <optgroup label="{$groupTitle}">
                                    {foreach $fields as $id => $title}
                                        <option value="{$id}"{if $settings[0].default_fields_id == $id} selected{/if}>{$title}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
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
