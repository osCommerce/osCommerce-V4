<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.TEXT_SYSTEM_UPDATES}</span> <a class="credit_amount_history" target="_blank" href="https://www.oscommerce.com/wiki/index.php?title=Change_Log">{$smarty.const.TEXT_CHANGELOG_INTRO}</a></h4>
    </div>
    <div class="widget-content" id="updates_box" style="min-height:400px">
        
    </div>
</div>
<script type="text/javascript">
    function showUpdateLog() {
        $.get("{Yii::$app->urlManager->createUrl('install/update-log')}" , function(data, status) {
            if (status == "success") {
                $('#updates_box').html(data);
            }
        },'html');
        return false;
    }
    function runQuery(force) {
        var dst_file_ignore = [];
        var selected_count = 0;
        if (force == 1) {
            $('#iframe').contents().find('input:checkbox:checked.dst_file_ignore').each(function(j, cb) {
                var aaa = $(cb).val();
                if (typeof(aaa) != 'undefined') {
                    selected_count++;
                    dst_file_ignore[selected_count] = aaa;
                }
            });
            $.post("{Yii::$app->urlManager->createUrl('install/save-ignore-list')}", { "dst_file_ignore" : dst_file_ignore } , function(data) {
                $('#updates_box').html('<iframe id="iframe" src="{Yii::$app->urlManager->createUrl('install/update-now')}?force='+force+'" style="width:100%;min-height:400px;border:0px;"></iframe>');
             },'json');
        } else {
            $('#updates_box').html('<iframe id="iframe" src="{Yii::$app->urlManager->createUrl('install/update-now')}?force='+force+'" style="width:100%;min-height:400px;border:0px;"></iframe>');
        }
        return false;
    }
    function checkActualStatus() {
        $.get("{Yii::$app->urlManager->createUrl('install/updates')}" , function(data, status) {
            if (status == "success") {
                $('#updates_box').html(data);
            }
        },'html');
    }
    $(document).ready(function(){
        checkActualStatus();
    });
</script>