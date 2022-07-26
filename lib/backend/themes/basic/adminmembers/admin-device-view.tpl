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
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="{if $adminRecord['admin_id'] > 0}1,2,3,4{else}2,3,4,5{/if}" data_ajax="adminmembers/admin-device-view-list?id={$adminRecord['admin_id']}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->DeviceTable as $tableItem}
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
    function doAdminDeviceBlockToggle(deviceId, element, adminId) {
        if ($(element).attr('is_blocked') === '0') {
            if (!confirm('{$smarty.const.MESSAGE_ADMIN_DEVICE_BLOCK_CONFIRM|replace:'\'':'\\\''}')) {
                deviceId = '';
            }
        }
        let id = parseInt((parseInt('{$adminRecord['admin_id']}') > 0) ? '{$adminRecord['admin_id']}' : adminId);
        if ((deviceId != '') && (id > 0)) {
            $.post("{Yii::$app->urlManager->createUrl('adminmembers/admin-device-block-toggle')}", { 'id': id, 'device': deviceId }, function(response) {
                if ((response != null) && (typeof(response.status) != 'undefined')) {
                    if (response.status == 'ok') {
                        if (element && (typeof(response.button) != 'undefined') && (typeof(response.blocked) != 'undefined')) {
                            $(element).text(response.button);
                            $(element).attr('is_blocked', response.is_blocked);
                            $(element).parents('td').prev().text(response.blocked);
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