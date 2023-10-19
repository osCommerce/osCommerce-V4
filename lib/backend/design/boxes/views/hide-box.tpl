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
                        <label for="">{$smarty.const.HIDE_PARENTS_IF_EMPTY}</label>
                        <select name="setting[0][hide_parents]" id="" class="form-control">
                            <option value=""{if $settings[0].hide_parents == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].hide_parents == '1'} selected{/if}>1</option>
                            <option value="2"{if $settings[0].hide_parents == '2'} selected{/if}>2</option>
                            <option value="3"{if $settings[0].hide_parents == '3'} selected{/if}>3</option>
                            <option value="4"{if $settings[0].hide_parents == '4'} selected{/if}>4</option>
                        </select>
                    </div>

                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_DISPLAY_WEIGHT}</label>
                        <select name="setting[0][display_weight]" id="" class="form-control">
                            <option value=""{if $settings[0].display_weight == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                            <option value="lb"{if $settings[0].display_weight == 'lb'} selected{/if}>{$smarty.const.TEXT_WEIGHT_UNIT_LB}</option>
                            <option value="kg"{if $settings[0].display_weight == 'kg'} selected{/if}>{$smarty.const.TEXT_WEIGHT_UNIT_KG}</option>
                            <option value="no"{if $settings[0].display_weight == 'no'} selected{/if}>{$smarty.const.TEXT_NONE}</option>
                        </select>
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