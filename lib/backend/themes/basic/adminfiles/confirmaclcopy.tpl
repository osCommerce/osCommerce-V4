<div class="or_box_head">{$smarty.const.IMAGE_COPY_TO}</div>
<form name="acl_copy" action="" method="post" id="acl_copy" onSubmit="return copyAcl();">
    <div class="col_desc">{$smarty.const.ENTRY_ACCESS_LEVELS_NAME}</div>
    <div class="choose-visibility">
        <select name="move_to_acl_id" class="col-md-12 select2 select2-offscreen">
            {foreach $aclList as $id => $text}
                <option value="{$id}">{$text}</option>
            {/foreach}
        </select>
    </div>
    <div class="btn-toolbar btn-toolbar-order">
        <button class="btn btn-copy btn-no-margin">{$smarty.const.IMAGE_COPY_TO}</button><button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <input type="hidden" name="item_id" value="{$obj->access_levels_id}">
    </div>
</form>
<script type="text/javascript">
$(function(){
   $('.select2').select2();
});
</script>