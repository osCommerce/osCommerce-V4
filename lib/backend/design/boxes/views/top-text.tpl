{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.DELIVERY_DAY}
    </div>
    <div class="popup-content">




        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.DELIVERY_DAY}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">


                    <div class="setting-row">
                        <label for="">{$smarty.const.IMAGE_VIEW}</label>
                        <select name="setting[0][view]" id="" class="form-control">
                            <option value=""{if $settings[0].view == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                            <option value="ticker"{if $settings[0].view == 'ticker'} selected{/if}>{$smarty.const.TEXT_TICKER}</option>
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