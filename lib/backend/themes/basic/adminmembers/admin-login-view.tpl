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
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="5" data_ajax="adminmembers/admin-login-view-list?id={$adminRecord['admin_id']}&type={$adminRecord['type']}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->LogTable as $tableItem}
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