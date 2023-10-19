<div class="or_box_head">{$smarty.const.TEXT_DESCRIPTION}</div>
<div class="row_or_wrapp" style="font-size: small; color: #424242;">
    <div class="row_or">
        <b>{$smarty.const.TEXT_DATE}:</b> <span style="color: dimgrey"><i><b>{$log->date}</b></i></span><br>
    </div>
    <div class="row_or">
        <b>{$smarty.const.EXT_ELV_TEXT_IP}:</b> <span style="color: dimgrey"><i><b>{$log->ip}</b></i></span><br>
    </div>
    <div class="row_or">
        <b>{$smarty.const.EXT_ELV_TEXT_ERROR_LEVEL}:</b> <span style="color: dimgrey"><i><b>{$log->level}</b></i></span><br>
    </div>
    <div class="row_or">
        <b>{$smarty.const.EXT_ELV_TEXT_CATEGORY}:</b> <span style="color: dimgrey"><i><b>{$log->category}</b></i></span><br>
    </div>
    <div class="row_or">
        <b>{$smarty.const.EXT_ELV_TEXT_ERROR_DESCRIPTION}:</b><br>
        <small><span style="color: dimgrey">{mb_strimwidth(htmlspecialchars($log->text), 0, 500, "...")}</span></small>
    </div>
    <br>
    <div class="btn-toolbar btn-toolbar-order">
        <button id="btn_modal" class="btn btn-no-margin" data-bs-toggle="modal" data-bs-target="#elv_modal">{$smarty.const.IMAGE_DETAILS}</button>
    </div>


</div>
<br>

<div id="elv_modal" class="modal fade bd-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 80vw">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" style="font-size: large;">{$log->file}</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color: #424242">
                <b>{$smarty.const.TEXT_DATE}:</b> <i><b>{$log->date}</b></i><br>
                <b>{$smarty.const.EXT_ELV_TEXT_IP}:</b> <i><b>{$log->ip}</b></i><br>
                <b>{$smarty.const.EXT_ELV_TEXT_ERROR_LEVEL}:</b> <i><b>{$log->level}</b></i><br>
                <b>{$smarty.const.EXT_ELV_TEXT_CATEGORY}:</b> <i><b>{$log->category}</b></i><br>
                <br>
                <b>{$smarty.const.TEXT_DESCRIPTION}:</b>
                <textarea rows="30" style="resize: none; cursor: pointer;" readonly="readonly">{$log->text}
                    {$log->description}
                </textarea>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function viewAsText()
    {
        window.open("{$app->urlManager->createUrl('error-log-viewer/view-as-text')}?file={$log->mask}", "_blank");
    }
</script>
