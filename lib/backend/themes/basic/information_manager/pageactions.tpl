<div class="or_box_head">{$iInfo->info_title}</div>
<div class="row_or_wrapp">
  <div class="row_or">
    <div>{$smarty.const.TEXT_DATE_ADDED}</div>
    <div>{\common\helpers\Date::date_format($iInfo->date_added, DATE_FORMAT_SHORT)}</div>
  </div>
  {if $iInfo->last_modified}
  <div class="row_or">
    <div>{$smarty.const.TEXT_LAST_MODIFIED}</div>
    <div>{\common\helpers\Date::date_format($iInfo->last_modified, DATE_FORMAT_SHORT)}</div>
  </div>
  {/if}
</div>
<div class="btn-toolbar btn-toolbar-order">
  <a class="btn btn-edit btn-primary btn-no-margin" href="{$iInfo->link_href_edit}">{IMAGE_EDIT}</a><button class="btn btn-delete" onclick="deleteInfo({$iInfo->information_id})">{IMAGE_DELETE}</button>
</div>
