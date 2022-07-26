<div class="row_or_wrapp">
    <div class="or_box_head">
        {$smarty.const.HEADING_TITLE}    
    </div>
    {if $cInfo['id']}
    <div class="row_or">
        <div class="label_value" style="text-align:left!important;">{$cInfo['location_name']}</div>
    </div>
    {/if}
</div>
    {if $cInfo['id']}
    <div class="btn-toolbar btn-toolbar-order">
			<a class="btn btn-edit btn-no-margin" href="{$editLink}">{$smarty.const.IMAGE_EDIT}</a><button class="btn btn-delete" onclick="deleteItem('{$cInfo['id']}')">{$smarty.const.IMAGE_DELETE}</button>
      </div>
    {/if}
