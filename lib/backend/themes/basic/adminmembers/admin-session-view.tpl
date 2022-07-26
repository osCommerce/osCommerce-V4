<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row">
        <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="1,2" data_ajax="adminmembers/admin-session-view-list?id={$adminRecord['admin_id']}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->SessionTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
<script type="text/javascript">
    function doAdminSessionDelete(computerId, element) {
        if ((computerId != '') && confirm('{$smarty.const.TEXT_LOGIN_SECURITY_KEY_DELETE_CONFIRM|replace:'\'':'\\\''}')) {
            $.post("{Yii::$app->urlManager->createUrl('adminmembers/admin-session-delete')}", { 'id': '{$adminRecord['admin_id']}', 'computer': computerId }, function(response) {
                if ((response != null) && (typeof(response.status) != 'undefined')) {
                    if (response.status == 'ok') {
                        if (element) {
                            $(element).parents('tr').remove();
                        } else {
                            window.location.reload();
                        }
                    }
                }
            }, 'json');
        }
        return false;
    }
</script>