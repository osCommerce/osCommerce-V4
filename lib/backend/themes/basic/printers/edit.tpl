<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>
{use class="yii\helpers\Html"}
<style>
 .scroll-jobs .pop-up-content{
    overflow-y: scroll;
    padding: 20px;
    height: 300px;
}
</style>
<div>
    <div class="alert fade in" style="display:none;">
        <i data-dismiss="alert" class="icon-remove close"></i>
        <span id="message_plce"></span>
    </div>
  {if {$messages|default:array()|@count} > 0}
    {foreach $messages as $type => $message}
        <div class="alert alert-{$type} fade in">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce">{$message}</span>
        </div>
    {/foreach}
  {/if}
    {Html::beginForm(['printers/service', 'id' => $service->id], 'post', ['name' => 'printers_form', 'enctype' => "multipart/form-data"])}
    
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs top_tabs_ul main_tabs">
            <li class="active" data-bs-toggle="tab" data-bs-target="#tab_main"><a><span>{$smarty.const.TEXT_SETTINGS}</span></a></li>
            {if $service->id}
            <li class="" data-bs-toggle="tab" data-bs-target="#tab_printers"><a><span>{$smarty.const.HEADING_TITLE}</span></a></li>
            {/if}
        </ul>
        <div class="tab-content">
            <div class="tab-pane topTabPane tabbable-custom active" id="tab_main">
                {Html::hiddenInput('id', $service->id)}
                <div>
                    <label>{$smarty.const.TEXT_CLOUD_SERVICE_NAME}</label>
                    {Html::activeTextInput($service, 'service', ['class' => 'form-control'])}
                </div>
                {if !$service->platform_id}
                <div>
                    <label>{$smarty.const.TABLE_HEADING_PLATFORM}</label>
                    {Html::activeDropDownList($service, 'platform_id', \yii\helpers\ArrayHelper::map($platforms, 'id', 'text'), ['class' => 'form-control'])}
                </div>
                {else}            
                    {Html::activeHiddenInput($service, 'platform_id')}
                {/if}
                
                <div>
                    <label>{$smarty.const.TEXT_KEY}</label>
                    <div class="file-upload">
                        <span class="btn btn-upload">{$smarty.const.IMAGE_UPLOAD}</span>
                            {\common\helpers\Html::activeFileInput($service, 'key', ['class' => 'file-upload', 'style' => "width: 0; height: 0; overflow: hidden; display: contents; "])}
                        <span>
                        {$service->key}{if $service->id && !$service->keyExists()} <span style="color:#ff0000">{$smarty.const.WARNING_NO_FILE_UPLOADED}</span>{/if}
                        </span>
                    </div>
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_printers">
                {Html::button(TEXT_GET_PRINTERS, ['class' => 'btn btn-load-printers', 'data-id' => $service->id])}
                <div class="cloud-printers">
                    
                </div>
                <br/>
                <div class="accepted-printers">
                    <label>{$smarty.const.TEXT_ACCEPTED_PRINTERS}</label>
                    <table class="table table-striped table-bordered table-hover table-responsive datatable table-printers" data_ajax="printers/acepted?id={$service->id}">
                        <thead>
                            <tr><th>{$smarty.const.TABLE_HEADING_ID}</th><th>{$smarty.const.TABLE_HEADING_PRINTER}</th><th>{$smarty.const.TABLE_HEADING_ACTION}</th></tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="btn-bar">
            <div class="btn-left"><a href="{\yii\helpers\Url::to(['printers/', 'platform_id' => $platform_id])}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
        </div>
    {Html::endForm()}
</div>
<script>
   (function($){
         $("a.popup").popUp();   
         var form = document.createElement('form');
         form.setAttribute('enctype', 'multipart/form-data');
         form.setAttribute('method', 'post');
         form.setAttribute('id', 'post-from');
         document.body.appendChild(form);
         $('.btn-upload').on('click', function(){
            $.each(form.children, function(i, e){
                form.removeChild(e);
            });                    
            $(this).parents('.file-upload').find('input[type=file]').trigger('click');
          });
        
        $('#post-from').fileupload();
        $('.file-upload').on('change', function(){
            var that = this;
            var filesList = $(this).prop('files');
            $('#post-from').fileupload('send', { files: filesList, url: 'upload/index' , paramName : 'file'})
            .success(function (result, textStatus, jqXHR) {
                $(that).next().html(filesList[0].name + '<input type="hidden" name="'+$(that).attr('name')+'" value="'+filesList[0].name+'">');
            });
        });
        
        $('.btn-load-printers').click(function(){
            if ($(this).data('id')){
                $('.cloud-printers').html('').addClass('preloader');
                $.post('printers/check-printers', { 'id': $(this).data('id') }, function(data){
                    $('.cloud-printers').removeClass('preloader');
                    if (data.hasOwnProperty('printers')){
                        $('.cloud-printers').html(data.printers);
                    } else {
                        if (data.hasOwnProperty('error')){
                            $('.cloud-printers').html(data.error.message);
                        } else {
                            $('.cloud-printers').html('{$smarty.const.DATATABLE_EMPTY_TABLE|escape:"javascript"}');
                        }
                    }
                }, 'json');
            }
        })
        
        
    })(jQuery)
    
    function errors(data){
        if (data.hasOwnProperty('message')){
            $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['type']).find('#message_plce').html('').append(data['message']);
        }
    }
    
    var start = true;
    function onClickEvent(obj, table) {
        if (start){
            setIconEvents();
            start = false;
        }
    }
    
    function onUnclickEvent(obj, table) {
        
    }
    
    function drawPrinters(){
        var table = $('.table-printers').DataTable();
        table.draw(false);
        setIconEvents();
    }
    
    function setIconEvents(){
        $('.accepted-printers').off().on('click', '.unlink-printer', function(){
            var id = $(this).data('id');
            bootbox.confirm('{$smarty.const.TEXT_UNLINK_PRINTER_CONFIRM|escape:javascript}', function(result){
                if (result){
                    $.post('printers/unlink', { 'id':id }, function(data){
                        drawPrinters();
                    }, 'json');
                }
            })
        }).on('click', '.test-printer', function(){
            var sid = $(this).data('sid');
            var pid = $(this).data('id');
            bootbox.prompt({
                title: "This is a prompt with an input!",
                inputType: 'text',
                callback: function (result) {
                    $.post('printers/test', { 'pid':pid, 'sid':sid, 'job':result }, function(data){
                        if (data.hasOwnProperty('message')){
                            alert(data.message);
                        }
                    },'json');
                }
            });
        });
        setTimeout(function(){
            $('.view-printer.popup').popUp();
            $('.printer-documents.popup').popUp();
            $('.printer-jobs.popup').popUp({ 'box_class':'scroll-jobs' });
        }, 1000);
        
        
    }
    </script>
            