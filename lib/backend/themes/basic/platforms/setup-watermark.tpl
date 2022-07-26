{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}

{$message}

<div id="platforms_management_data">
    <form action="edit.tpl" name="save_item_form" id="save_item_form" enctype="multipart/form-data" onsubmit="return saveItem();">

{include file="./edit/watermark.tpl"}

     {Html::input('hidden', 'id', $pInfo->platform_id)}
    <div class="btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
</form>

</div>

<script>
    function saveItem() {
        $.post("{$app->urlManager->createUrl('platforms/setup-watermark')}", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#platforms_management_data').html(data);
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
    function backStatement() {
        window.history.back();
        return false;
    }
</script>