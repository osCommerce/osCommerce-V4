<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-upload"></i><span id="easypopulate_upload_files_title">{$smarty.const.TEXT_SYSTEM_UPDATES}</span> <a class="credit_amount_history" target="_blank" href="https://wiki.oscommerce.com/index.php?title=Change_Log">{$smarty.const.TEXT_CHANGELOG_INTRO}</a></h4>
    </div>
    <div class="widget-content" id="updates_box">
        
    </div>
</div>
<script type="text/javascript">
    function runQuery() {
        $.get("{Yii::$app->urlManager->createUrl('install/update-now')}", 
            function (data, status) {
                if (status == "success") {
                    $('#updates_box').html(data);
                }
            }, "html"
        );
        return false;
    }
    $(document).ready(function(){
        $.get("{Yii::$app->urlManager->createUrl('install/updates')}" , function(data, status) {
            if (status == "success") {
                $('#updates_box').html(data);
            }
        },'html');
    });
</script>