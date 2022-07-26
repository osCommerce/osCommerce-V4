{use class="\common\helpers\Html"}
<div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_COPY_TO}</div>
<form name="products" action="" method="post" id="products_copy" onSubmit="return copyProduct();">
    <div class="col_title">{$smarty.const.TEXT_INFO_COPY_TO_INTRO}</div>
    <div class="col_desc">{$smarty.const.TEXT_INFO_CURRENT_CATEGORIES}</div>
    <div class="col_desc">{\common\helpers\Categories::output_generated_category_path($pInfo->products_id, 'product')}</div>
    <div class="col_desc">{$smarty.const.TEXT_CATEGORIES}</div>
    <div class="col_desc"><label>{tep_draw_pull_down_menu('categories_id', \common\helpers\Categories::get_category_tree(), $pInfo->categories_id)}</label></div>
    <div class="col_desc">{$smarty.const.TEXT_HOW_TO_COPY}</div>
    <div class="col_desc"><label>{Html::radio('copy_as', true, ['value' => 'link', 'class' => 'copy-as'])} {$smarty.const.TEXT_COPY_AS_LINK}</label></div>
    <div class="col_desc"><label>{Html::radio('copy_as', false, ['value' => 'duplicate', 'class' => 'copy-as'])} {$smarty.const.TEXT_COPY_AS_DUPLICATE}</label></div>
    <div class="col_desc copy-dup" id='copy_dup_selected' style="display: none;">
        <div class="col_desc"><label>{Html::checkbox('copy_categories', false, ['value' => 1])} {$smarty.const.TEXT_COPY_CATEGORIES}</label></div>

    {if \common\helpers\Attributes::has_product_attributes($pInfo->products_id, true)}
        {*<div class="col_desc">{$smarty.const.TEXT_COPY_ATTRIBUTES_ONLY}</div>*}
        <div class="col_desc">{$smarty.const.TEXT_COPY_ATTRIBUTES}</div>
        <div class="col_desc"><label>{tep_draw_radio_field('copy_attributes', '1', true)} {$smarty.const.TEXT_COPY_ATTRIBUTES_YES}</label></div>
        <div class="col_desc"><label>{tep_draw_radio_field('copy_attributes', '0')} {$smarty.const.TEXT_COPY_ATTRIBUTES_NO}</label></div>
    {/if}
    </div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-copy btn-no-margin">{$smarty.const.IMAGE_COPY}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="products_id" value="{$pInfo->products_id}">
        <input type="hidden" name="from_categories_id" value="{$pInfo->categories_id}">
    </div>
</form>
<script type="text/javascript">
(function($){
    $('.copy-as').on('click', function() {

        if ($(this).val() == 'duplicate') {
            $('#copy_dup_selected').show();
            $('#copyCategories').uniform();
        } else {
            $('#copy_dup_selected').hide();
        }
    });
})(jQuery);
</script>