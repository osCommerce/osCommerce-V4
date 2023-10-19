<div class="or_box_head">{$smarty.const.EXT_ELV_TEXT_INFO}</div>
<div class="row_or_wrapp" style="font-size: small; color: #424242;">
    <div class="row_or">
        {$smarty.const.EXT_ELV_TEXT_FILENAME}: <span><b>{$file->name}</b></span><br>
    </div>
    <div class="row_or">
        {$smarty.const.EXT_ELV_TEXT_LOCATION}: <span><b>{$file->fullPath}</b></span><br>
    </div>
    <div class="row_or">
        {$smarty.const.EXT_ELV_TEXT_MODIFIED}: <span><b>{$file->date}</b></span><br>
    </div>
</div>
<br>
<div class="btn-toolbar btn-toolbar-order">
    <form action="{\Yii::$app->urlManager->createUrl(['error-log-viewer/view'])}" method="get" id="view">
        <input type="hidden" value="{$file->mask}" name="log" id="file">
    </form>
    <input type="submit" onclick="submit()" class="btn btn-no-margin" value="{$smarty.const.IMAGE_VIEW}">
    <button class="btn btn-delete btn-no-margin" onclick="deleteLog()">{$smarty.const.IMAGE_DELETE}</button>
    <button class="btn btn-no-margin" onclick="viewAsText()"><i class="icon-file-text-alt"></i> {$smarty.const.EXT_ELV_TEXT_VIEW_AS_TEXT}</button>

</div>
<script type="text/javascript">
    function submit(){
        $('#view').submit();
    }
</script>