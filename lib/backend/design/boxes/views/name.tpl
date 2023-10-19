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
                        <label for="">{$smarty.const.SHOW_NAME_AND_HEADING}</label>
                        <select name="setting[0][show_heading]" id="" class="form-control">
                            <option value=""{if $settings[0].show_heading == ''} selected{/if}>{$smarty.const.FIRST_NAME_THEN_H1}</option>
                            <option value="h1_name"{if $settings[0].show_heading == 'h1_name'} selected{/if}>{$smarty.const.FIRST_H1_THEN_NAME}</option>
                            <option value="h1"{if $settings[0].show_heading == 'h1'} selected{/if}>{$smarty.const.ONLY_H1}</option>
                            <option value="name_in_div"{if $settings[0].show_heading == 'name_in_div'} selected{/if}>{$smarty.const.ONLY_NAME_IN_DIV}</option>
                            <option value="name_in_h1"{if $settings[0].show_heading == 'name_in_h1'} selected{/if}>{$smarty.const.ONLY_NAME_IN_H1}</option>
                            <option value="name_in_h2"{if $settings[0].show_heading == 'name_in_h2'} selected{/if}>{$smarty.const.ONLY_NAME_IN_H2}</option>
                            <option value="name_in_h3"{if $settings[0].show_heading == 'name_in_h3'} selected{/if}>{$smarty.const.ONLY_NAME_IN_H3}</option>
                            <option value="name_in_h4"{if $settings[0].show_heading == 'name_in_h4'} selected{/if}>{$smarty.const.ONLY_NAME_IN_H4}</option>
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