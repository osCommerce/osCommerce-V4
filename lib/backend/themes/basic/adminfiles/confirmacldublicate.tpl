<div class="or_box_head">{$smarty.const.IMAGE_DUBLICATE}</div>
<form name="acl_dublicate" action="" method="post" id="acl_dublicate" onSubmit="return dublicateAcl();">
    <div class="col_desc">{$smarty.const.ENTRY_ACCESS_LEVELS_NAME}</div>
    <div class="choose-visibility">
        <input type="text" name="new_title" value="{$obj->access_levels_name}(copy)" class="col-md-12" />
    </div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-copy btn-no-margin">{$smarty.const.IMAGE_DUBLICATE}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="item_id" value="{$obj->access_levels_id}">
    </div>
</form>