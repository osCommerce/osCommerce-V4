<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.TEXT_SYSTEM_UPDATES}</span> <a class="credit_amount_history" target="_blank" href="https://wiki.oscommerce.com/index.php?title=Change_Log">{$smarty.const.TEXT_CHANGELOG_INTRO}</a></h4>
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
    function runQuery() {
        $('#updates_box').html('<iframe src="{Yii::$app->urlManager->createUrl('install/update-now')}" style="width:100%;min-height:400px;border:0px;"></iframe>');
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