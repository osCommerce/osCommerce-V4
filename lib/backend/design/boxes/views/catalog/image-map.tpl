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

                <li class="active"><a href="#product" data-toggle="tab">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
                <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
                <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="product">
                    {include '../include/listings-product.tpl'}
                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

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