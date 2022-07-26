<div class="row" id="download_files" style="_display: none;">
    
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="cache_control_title">{$smarty.const.TEXT_EXPORT}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="cache_control_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div id="download_control_data">
                
            </div>
            <div class="widget-content fields_style">
                <form name="download_control_form" action="{$app->urlManager->createUrl('install/download')}" method="post">
                    <label class="checkbox">
                        <input name="menu" type="checkbox" class="uniform" value="1"> {$smarty.const.BOX_ADMINISTRATOR_MENU}
                    </label>
                    <label class="checkbox">
                            <input name="groups" type="checkbox" class="uniform" value="1"> {$smarty.const.BOX_ADMINISTRATOR_BOXES}
                    </label>
                    <label class="checkbox">
                            <input name="members"  type="checkbox" class="uniform" value="1"> {$smarty.const.BOX_ADMINISTRATOR_MEMBERS}
                    </label>
                    <input type="submit" class="btn btn-primary" value="{$smarty.const.IMAGE_DOWNLOAD}" >
                </form>
            </div>
        </div>
    </div>
                
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="cache_control_title">{$smarty.const.TEXT_IMPORT}</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="cache_control_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div id="upload_control_data">
                
            </div>
            <div class="widget-content fields_style">
                <a href="javascript:void(0)" class="btn btn-primary btn-import">{$smarty.const.IMAGE_UPLOAD}</a>
            </div>
        </div>
    </div>
                

                
</div>
                
<script type="text/javascript">
function downloadContent() {
    $.post("{$app->urlManager->createUrl('install/download')}", $('form[name=download_control_form]').serialize(), function(data, status){
        if (status == "success") {
            $('#download_control_data').append(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
$(document).ready(function(){ 
    $('.btn-import').each(function() {
        $(this).dropzone({
          url: '{Yii::$app->urlManager->createUrl('install/upload')}',
          success: function(){
            location.reload();
          }
        })
    });
});
</script>   
