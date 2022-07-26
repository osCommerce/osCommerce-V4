{use class="\yii\helpers\Html"}
<div class="row_or_wrapp">
    <div class="or_box_head">
        {if $cInfo->id}
            {$smarty.const.TEXT_EDIT_REDIRECT}
        {else}
            {$smarty.const.TEXT_CREATE_REDIRECT}
        {/if}
    </div>
    <form method="post" id="redirect" name="redirect">
        {Html::input('hidden', 'item_id', $cInfo->id )}
        {Html::input('hidden', 'platform_id', $cInfo->platform_id )}
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td class="label_name" valign="top" align="left">{$smarty.const.TABLE_HEADING_OLD_URL}:</td>
            </tr>
            <tr>
                <td class="label_value">
                    {Html::input('text', 'old_url', $cInfo->old_url, ['class' => 'form-control'] )}
                </td>
            </tr>
            <tr>
                <td class="label_name" valign="top" align="left">{$smarty.const.TABLE_HEADING_NEW_URL}:</td>
            </tr>
            <tr>
                <td class="label_value">
                    {Html::input('text', 'new_url', $cInfo->new_url, ['class' => 'form-control'] )}
                </td>
            </tr>
        </table>

        <div class="btn-toolbar btn-toolbar-order">
            <input type="button" value="{if $cInfo->id}{$smarty.const.IMAGE_UPDATE}{else}{$smarty.const.IMAGE_SAVE}{/if}" class="btn btn-no-margin" onclick="redirectSave();"><input type="button" value="{$smarty.const.IMAGE_CANCEL}" class="btn btn-cancel" onclick="resetStatement()">
        </div>
    </form>
</div>