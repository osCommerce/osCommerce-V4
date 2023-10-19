{use class="Yii"}
{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
{Info::addBlockToPageName($list_type)}
{Info::addBoxToCss('products-listing')}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TEXT_FILTERS}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.HEADING_TYPE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">


                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_SELECT_CATEGORY_PAGE}</label>
                        {Html::dropDownList('setting[0][selectCatalogPageLastListByIdBlock]', $settings[0].selectCatalogPageLastListByIdBlock, array_merge([''], $catalogPages),  ['class' => 'form-control'])}
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_COUNT_ON_PAGE}</label>
                        {Html::input('text', 'setting[0][limitInformationLastListByIdPageBlock]', $settings[0].limitInformationLastListByIdPageBlock, ['class' =>'form-control'])}
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