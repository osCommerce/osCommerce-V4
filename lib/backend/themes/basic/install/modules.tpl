{use class="yii\helpers\Html"}
<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.UPLOAD_EP_FILES}</span>
        </h4>
    </div>
    <div class="widget-content fields_style" id="easypopulate_upload_files_data">
        <form name="frmUpload" id="frmUpload" enctype="multipart/form-data" action="" METHOD="POST">
            <div class="row">
                <div class="col-md-4">
                    <br><br><a href="{$app->urlManager->createUrl('install/cleanup-local-storage')}" onclick="return confirm('{$smarty.const.TEXT_CLEANUP_INTRO|escape:javascript}')" class="btn btn-confirm">Cleanup my local storage</a>
                </div>
                <div class="col-md-8">
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
<div class="widget box">
    <div class="widget-content">
        <div class="dataTable_header_float_content"><button type="button" class="btn js-reload-jobs"><i class="icon icon-refresh"></i> Refresh</button></div>
        <table id="tblFiles" class="ep-file-list table table-striped table-selectable table-checkable table-hover table-responsive table-bordered -datatable tab-cust tabl-res double-grid"
            checkable_list="" data-directory_id="{$currentDirectory->directory_id|default:null}">
            <thead>
            <tr>
                <th>{$smarty.const.TEXT_INFO_DATE_ADDED1}</th>
                <th>{$smarty.const.ICON_FILE}</th>
                {*<th>{$smarty.const.TABLE_HEADING_FILE_SIZE}</th>*}
                <th>{$smarty.const.TEXT_APPLICATION}</th>
                <th>{$smarty.const.HEADING_TYPE}</th>
                <th>{$smarty.const.TEXT_REQUIREMENTS}</th>
                <th>{$smarty.const.TEXT_STATE}</th>
                <th>{$smarty.const.TABLE_HEADING_ACTION}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<style>
.ep-file-list a.remove-ast{ text-decoration: none; }
a.job-button{ text-decoration: none; margin: 0 4px; font-size: 1.1em; }
a.job-button:hover{ text-decoration: none; }
.job-button .icon-trash{ color: #ff0000 }
.job-button .icon-cog, .job-button .icon-reorder{ color: #008be8 }
.job-button .icon-play{ color: #006400 }
</style>
<script type="text/javascript">
function createLoader() {
    console.log('create');
    $('#content > .container').addClass('hided-box').append('<div class="hided-box-holder" style="position: fixed"><div class="preloader"></div></div>');
}
function destroyLoader() {
    console.log('destroy');
    $('#content > .container').removeClass('hided-box');
    $('.hided-box-holder').remove();
}
function file_deploy(fileName, chooseType) {
    if (chooseType == 2) { // install new language
        bootbox.dialog({
            message: '<div class="installPopupArea">{$smarty.const.TEXT_NEW_LANGUAGE_INTRO|escape:javascript}</div>',
            title: "{$smarty.const.TEXT_NEW_LANGUAGE|escape:javascript}",
            buttons: {
                done:{
                    label: "{$smarty.const.TEXT_BTN_OK|escape:javascript}",
                    className: "btn-cancel",
                    callback: function() {
                        createLoader();
                        $.ajax({
                            url:'install/deploy-file',
                            type: 'POST',
                            cache: false,
                            data: {
                                name: fileName,
                            },
                            success:function(data) {
                                alertMessage('<div class="popup-content">'+data.text+'</div>');
                                if ( data.status=='ok' ) {
                                    $('#tblFiles').trigger('reload');
                                }
                            }
                        }).always(destroyLoader);
                    }
                }
            }
        });
        return false;
    }
    if (chooseType == 3) { // update language settings
        bootbox.dialog({
            message: '<div class="installPopupArea">{$smarty.const.TEXT_UPDATE_LANGUAGE_SETTINGS|escape:javascript}: {Html::dropDownList('locale','1',['1' => 'Yes', '0' => 'No'],['class'=>'form-control'])|escape:javascript}</div>',
            title: "{$smarty.const.TEXT_UPDATE_LANGUAGE|escape:javascript}",
            buttons: {
                done:{
                    label: "{$smarty.const.TEXT_BTN_OK|escape:javascript}",
                    className: "btn-cancel",
                    callback: function() {
                        createLoader();
                        var locale = $('select[name="locale"]').val();
                        $.ajax({
                            url:'install/deploy-file',
                            type: 'POST',
                            cache: false,
                            data: {
                                name: fileName,
                                locale: locale
                            },
                            success:function(data) {
                                alertMessage('<div class="popup-content">'+data.text+'</div>');
                                if ( data.status=='ok' ) {
                                    $('#tblFiles').trigger('reload');
                                }
                            }
                        }).always(destroyLoader);
                    }
                }
            }
        });
        return false;
    }
    if (chooseType == 1) { // platform
        bootbox.dialog({
            message: '<div class="installPopupArea">{$smarty.const.BOX_PLATFORMS}: {Html::dropDownList('platform',\common\classes\platform::defaultId(),$platforms,['class'=>'form-control'])|escape:javascript}</div>',
            title: "Choose platform",
            buttons: {
                done:{
                    label: "{$smarty.const.TEXT_BTN_OK}",
                    className: "btn-cancel",
                    callback: function() {
                        createLoader();
                        var platform = $('select[name="platform"]').val();
                        $.ajax({
                            url:'install/deploy-file',
                            type: 'POST',
                            cache: false,
                            data: {
                                name: fileName,
                                platform: platform
                            },
                            success:function(data) {
                                alertMessage('<div class="popup-content">'+data.text+'</div>');
                                if ( data.status=='ok' ) {
                                    $('#tblFiles').trigger('reload');
                                }
                            }
                        }).always(destroyLoader);
                    }
                }
            }
        });
        return false;
    }
    createLoader();
    $.ajax({
        url:'install/deploy-file',
        type: 'POST',
        cache: false,
        data: {
            name: fileName
        },
        success:function(data) {
            alertMessage('<div class="popup-content">'+data.text+'</div>');
            if ( data.status=='ok' ) {
                $('#tblFiles').trigger('reload');
            }
        }
    }).always(destroyLoader);
    return false;
}
function file_remove(fileName) {
    bootbox.confirm('Remove file?',function(process){
        if ( !process ) return;

        $.ajax({
            url:'install/remove-file',
            type: 'POST',
            cache: false,
            data: {
                name: fileName
            },
            success:function(data) {
                alertMessage('<div class="popup-content">'+data.text+'</div>');
                if ( data.status=='ok' ) {
                    $('#tblFiles').trigger('reload');
                }
            }
        });

    });

    return false;
}
function file_revert(fileName) {
    bootbox.confirm('Revert deployment?',function(process){
        if ( !process ) return;

        $.ajax({
            url:'install/revert-file',
            type: 'POST',
            cache: false,
            data: {
                name: fileName
            },
            success:function(data) {
                alertMessage('<div class="popup-content">'+data.text+'</div>');
                if ( data.status=='ok' ) {
                    $('#tblFiles').trigger('reload');
                }
            }
        });

    });

    return false;
}

$('#tblFiles').on('reload',function(event, resetPage) {
    if (typeof resetPage === 'undefined') resetPage = false;
    $('#tblFiles').DataTable().ajax.reload(null,resetPage);
});

$(document).ready(function(){
    var table = $('#tblFiles').DataTable( {
        "paging": false,
        "serverSide": true,
        "processing": true,
        "ajax": {
            "url": '{$job_list_url}',
            "data":function(data, settings){
                data.directory_id = $('#tblFiles').attr('data-directory_id');
            }
        },
        "ordering": false
    } );
    
    $('.js-reload-jobs').on('click',function(){
        $('#tblFiles').trigger('reload');
    });
    
});
</script>