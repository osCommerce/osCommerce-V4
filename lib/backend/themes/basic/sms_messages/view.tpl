{use class="\yii\helpers\Url"}
<div class="row_or_wrapp">
    <div class="or_box_head">
        {$smarty.const.HEADING_TITLE}    
    </div>
    {if $mInfo->sms_default_message_id}    
    <div class="row_or">
        <div class="label_value" style="text-align:left!important;">{$mInfo->by_language[$languages_id]['sms_default_message_name']}</div>
    </div>
    {/if}
</div>
    {if $mInfo->sms_default_message_id}    
    <div class="btn-toolbar">
        <a href="{Url::to(['sms_messages/edit', 'item_id' => $mInfo->sms_default_message_id, 'platform_id' => {$mInfo->platform_id}])}" class="btn btn-edit btn-no-margin">{$smarty.const.IMAGE_EDIT}</a>
		<button class="btn btn-delete" onclick="deleteMessage('{$mInfo->sms_default_message_id}')">{$smarty.const.IMAGE_DELETE}</button>
      </div>
    {/if}
