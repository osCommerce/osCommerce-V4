{use class="common\helpers\Html"}
<div class="or_box_head">{$smarty.const.TEXT_COPY_ATTRIBUTES_INTRO}</div>
<form name="products" action="" method="post" id="products_attr_copy" onSubmit="return copyProductAttr();">
    <div class="col_desc">{$smarty.const.TEXT_COPY_ATTRIBUTES_FROM_ID}<span class="colon">:</span> {$pInfo->products_id}</div>
    <div class="col_desc">{$pInfo->products_name}</div>
    <div class="col_desc">{$smarty.const.TEXT_COPY_ATTRIBUTES_TO_ID}<span class="colon">:</span> </div>
    <div class="search-product"><input type="text" class="form-control" name="products_name" value="" placeholder="{$smarty.const.START_TYPING_PRODUCT_NAME}"/></div>
    {*<div class="col_desc">{tep_draw_input_field('copy_to_products_id', $copy_to_products_id, 'size="3" class="form-control"')}</div>*}
    <div class="col_desc"><label>{$smarty.const.TEXT_COPY_ATTRIBUTES_CLEANUP}{Html::checkbox('copy_attributes_delete_first', false, ['value' => 1, 'class'=>"uniform"])}</label></div>
    <div class="col_desc"><label>{$smarty.const.TEXT_COPY_ATTRIBUTES_SAVE_DUPLICATES}{Html::checkbox('copy_attributes_duplicates_skipped', false, ['value' => 1, 'class'=>"uniform"])}</label></div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-copy btn-no-margin">{$smarty.const.IMAGE_COPY}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="products_id" value="{$pInfo->products_id}">
        <input type="hidden" name="copy_to_products_id" id="copy_to_products_id" value="">
    </div>
</form>
