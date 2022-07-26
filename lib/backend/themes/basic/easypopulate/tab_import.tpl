{use class="yii\helpers\Html"}
<div class="widget box" {if $currentDirectory->cron_enabled}style="display:none"{/if}>
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.UPLOAD_EP_FILES}</span>
        </h4>
    </div>
    <div class="widget-content fields_style" id="easypopulate_upload_files_data">
        <form name="frmUpload" id="frmUpload" enctype="multipart/form-data" action="" METHOD="POST">
            <div class="row">
                <div class="col-md-4 text-right">
                    <b>{$smarty.const.TEXT_UPLOAD_FILE_SELECT}</b>
                </div>
                <div class="col-md-8">
                    <p>
                    {Html::dropDownList('file_type', $selected_type, $importProviders['items'], $importProviders['options'])}
                    </p>
                    <p>
                    <input name="data_file" type="file" size="50" data-accept=".*">
                    </p>
                    <button type="submit" class="btn btn-primary js-btn_upload" >{$smarty.const.IMAGE_UPLOAD}</button>
                </div>
            </div>
            <div class="js-mappings" style="display: none"></div>
        </form>
        <div class="import_progress">
            <div class="progress_state">&nbsp;</div>
            <div class="js-upload-progress progress_bar"></div>
        </div>
        <div id="easypopulate_management_data"></div>

    </div>
</div>   