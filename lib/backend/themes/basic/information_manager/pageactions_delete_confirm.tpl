<div class="or_box_head">{$iInfo->info_title}</div>
<div class="row_or_wrapp">
  Are you sure? Remove {$iInfo->info_title} ?
</div>
<div class="btn-toolbar btn-toolbar-order">
  <button class="btn btn-delete btn-no-margin" onclick="confirmedDeletePage({$iInfo->information_id})">{IMAGE_DELETE}</button><a class="btn btn-edit btn-primary" href="javascript:void(0)" onclick="return loadPageAction({$iInfo->information_id})">{IMAGE_CANCEL}</a>
</div>
