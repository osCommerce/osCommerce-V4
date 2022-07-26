<div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_MOVE_PRODUCT}</div>
<form name="products" action="" method="post" id="products_move" onSubmit="return moveProduct();">
    <div class="col_title">{sprintf($smarty.const.TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name)}</div>
    <div class="col_desc">{$smarty.const.TEXT_INFO_CURRENT_CATEGORIES}</div>
    <div class="col_desc">{\common\helpers\Categories::output_generated_category_path($pInfo->products_id, 'product')}</div>
    <div class="col_desc">{sprintf($smarty.const.TEXT_MOVE, $pInfo->products_name)}</div>
    <div class="choose-visibility">
        <select name="move_to_category_id" class="col-md-12 select2 select2-offscreen">
            {foreach $categoryTree as $category}
                <option value="{$category.id}">{$category.text}</option>
            {/foreach}
        </select>
        {*tep_draw_pull_down_menu('move_to_category_id', \common\helpers\Categories::get_category_tree(), $pInfo->categories_id)*}</div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-move btn-no-margin">{$smarty.const.IMAGE_MOVE}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="products_id" value="{$pInfo->products_id}">
        <input type="hidden" name="categories_id" value="{$pInfo->categories_id}">
    </div>
</form>
<script type="text/javascript">
$(function(){
   $('.select2').select2();
});
</script>