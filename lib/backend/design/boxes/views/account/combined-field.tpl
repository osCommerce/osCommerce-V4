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
                        <label for="">Fields</label>
                        <input type="text" value="{$settings[0].fields}" name="setting[0][fields]" class="form-control" style="width: 100%">
                    </div>

                    <div class="setting-row">
                        <label for="">Show label</label>
                        <select name="setting[0][no_label]" id="" class="form-control">
                            <option value=""{if $settings[0].no_label == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="1"{if $settings[0].no_label == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
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
