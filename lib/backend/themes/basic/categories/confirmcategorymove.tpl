<div class="or_box_head">{$smarty.const.TEXT_INFO_HEADING_MOVE_CATEGORY}</div>
<form name="categories" action="" method="post" id="categories_move" onSubmit="return moveCategory();">
    <div class="col_title">{sprintf($smarty.const.TEXT_MOVE_CATEGORIES_INTRO, $cInfo->categories_name)}</div>
    <div class="col_desc">{sprintf($smarty.const.TEXT_MOVE, $cInfo->categories_name)}</div>
    <div class="choose-visibility">
        <select name="move_to_category_id" class="col-md-12 select2 select2-offscreen">
            {foreach $categoryTree as $category}
                <option value="{$category.id}">{$category.text}</option>
            {/foreach}
        </select>
        {*tep_draw_pull_down_menu('move_to_category_id', \common\helpers\Categories::get_category_tree(0, '', $cInfo->categories_id), $cInfo->categories_id)*}
    </div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-move btn-no-margin">{$smarty.const.IMAGE_MOVE}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="categories_id" value="{$cInfo->categories_id}">
    </div>
</form>
<script type="text/javascript">
$(function(){
   $('.select2').select2();
});
</script>