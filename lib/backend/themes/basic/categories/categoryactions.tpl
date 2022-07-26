<div class="or_box_head">{$cInfo->categories_name}</div>
<div class="row_or_wrapp">
<div class="row_or">
    <div>{$smarty.const.TEXT_DATE_ADDED}</div>
    <div>{\common\helpers\Date::date_short($cInfo->date_added)}</div>
</div>
<div class="row_or">
    <div>{$smarty.const.TEXT_LAST_MODIFIED}</div>
    <div>{\common\helpers\Date::date_short($cInfo->last_modified)}</div>
</div>
<div class="row_or">
    <div>{$smarty.const.TEXT_SUBCATEGORIES}</div>
    <div>{$cInfo->childs_count}</div>
</div>
<div class="row_or">
    <div>{$smarty.const.TEXT_PRODUCTS}</div>
    <div>{$cInfo->products_count}</div>
</div>
</div>
<div class="btn-toolbar btn-toolbar-order">
{if \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_EDIT'])}    
    <a class="btn btn-primary btn-process-order btn-edit" href="{Yii::$app->urlManager->createUrl(['categories/categoryedit', 'categories_id' => $cInfo->categories_id])}">{IMAGE_EDIT}</a>
{/if}
{if \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_MOVE'])}<button class="btn btn-no-margin btn-move" onclick="confirmMoveCategory({$cInfo->categories_id})">{IMAGE_MOVE}</button>{/if}{if \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_DELETE'])}<button class="btn btn-delete" onclick="confirmDeleteCategory({$cInfo->categories_id})">{IMAGE_DELETE}</button>{/if}
{if \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_MOVE']) && $cInfo->hasGrouppedProducts}<button class="btn btn-process-order btn-sort" onclick="confirmSortGroupped({$cInfo->categories_id}, {intval($cInfo->childs_count)})">{TEXT_REINDEX_GROUPPED}</button>{/if}
</div>
{if $cInfo->eventInfo}{$cInfo->eventInfo}{/if}