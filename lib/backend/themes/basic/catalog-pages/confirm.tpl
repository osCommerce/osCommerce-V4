<div class="or_box_head" style="width: 100%; text-align: center;">{$smarty.const.TEXT_INFO_HEADING_DELETE_OPERATION_TEXT}</div>
<div class="row_or"><div style="width: 100%; text-align: center;">{$smarty.const.TEXT_INFO_HEADING_DELETE_TEXT}</div></div>
<div class="row_or"><div>&nbsp;</div></div>
<div class="row_or"><div style="width: 100%; text-align: left;"><b>{$catalogPage.descriptionLanguageId.name}</b></div></div>
<div class="btn-toolbar btn-toolbar-order" style="width: 100%; text-align: center;">
    <input type="submit" class="btn btn-primary" style="display: inline-block; width: 40%" value="{$smarty.const.IMAGE_DELETE}" onclick="deleteItemConfirm({$catalogPage.catalog_pages_id},true)">
    <input type="button" class="btn btn-cancel"  style="display: inline-block; width: 40%" value="{$smarty.const.IMAGE_CANCEL}" onClick="return resetStatement()">
</div>