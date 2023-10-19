{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_BLOCK}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="type">

                    <div class="setting-row">
                        <label for="">Show text</label>
                        <select name="setting[0][show_text]" id="" class="form-control">
                            <option value=""{if $settings[0].show_text == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                            <option value="no"{if $settings[0].show_text == 'no'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                        </select>
                    </div>

                        {$invoice_part}

                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>