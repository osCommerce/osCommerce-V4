
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="">
    <!--=== Page Content ===-->
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
      <ul class="nav nav-tabs" id='osg'>
          {foreach $types as $id => $type}
            <li class="{if $id==$type_id} active {/if}"><a class="js_link_platform_modules_select" href="{Yii::$app->urlManager->createUrl(['orders_status/', 'type_id' => $id])}" data-type_id="{$id}"><span>{$type}</span></a></li>
          {/foreach}
      </ul>
    </div>
    <div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>
                <div class="ord_status_filter_row">
                    <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                        <input type="hidden" name="row" id="row_id" value="{$row}" />
                        <input type="hidden" name="type_id" value="{$type_id}" />
                        {$app->controller->view->filterStatusGroups}
                    </form>
                </div>
                {if {$messages|@count} > 0}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message['message']}</span>
                        </div>
                    {/foreach}
                {/if}
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="orders_status/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->StatusTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>

                </form>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        function switchOffCollapse(id) {
            if ($("#"+id).children('i').hasClass('icon-angle-down')) {
                $("#"+id).click();
            }
        }

        function switchOnCollapse(id) {
            if ($("#"+id).children('i').hasClass('icon-angle-up')) {
                $("#"+id).click();
            }
        }

        function resetStatement() {
            $("#status_management").hide();
            switchOnCollapse('status_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            $(window).scrollTop(0);
            return false;
        }
        function setFilterState() {
            orig = $('#filterForm').serialize();
            var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
            window.history.replaceState({ }, '', url);
        }
        function onClickEvent(obj, table) {
            $("#status_management").hide();
            $('#status_management_data .scroll_col').html('');
            $('#row_id').val(table.find(obj).index());
            var orders_status_id = $(obj).find('input.cell_identify').val();
            setFilterState();
            var toGet = '';
            /*
             try {
              table = $(table).DataTable();
              var tmp = { 'order': table.order(),
                          'page': table.page(),
                          'search': { 'value': table.search() }
                          };
              //console.log( tmp );
              toGet = $.param(tmp) + '&';
            } catch (e) { console.log(e) }
             */

            $.post("{Yii::$app->urlManager->createUrl('orders_status/statusactions')}" + '?' + toGet + $('#filterForm').serialize(), { 'orders_status_id' : orders_status_id },
              function(data, status){
                  if (status == "success") {
                      $('#status_management_data .scroll_col').html(data);
                      $("#status_management").show();
                      // window.history.replaceState('', '', 'orders_status?orders_status_id=' + orders_status_id);
                  } else {
                      alert("Request error.");
                  }
              },"html");
        }

        function onUnclickEvent(obj, table) {
            $("#status_management").hide();
            var event_id = $(obj).find('input.cell_identify').val();
            var type_code = $(obj).find('input.cell_type').val();
            $(table).DataTable().draw(false);
        }

/** not used?? */
        function statusEdit(id){
            window.location.href = "orders_status/edit?orders_status_id=" + id;
            return false;
        }
/** not used?? */
        function statusSave(id){
            $.post("orders_status/save?orders_status_id="+id, $('form[name=status]').serialize(), function(data, status){
                if (status == "success") {
                    //$('#status_management_data').html(data);
                    //$("#status_management").show();
                    $('.alert #message_plce').html('');
                    $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                    resetStatement();
                    switchOffCollapse('status_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"json");
            return false;
        }

        function statusDelete(id){
            if (confirm('Do you confirm?')){
                $.post("orders_status/delete", { 'orders_status_id' : id}, function(data, status){
                    if (status == "success") {
                        //$('.alert #message_plce').html('');
                        //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                        if (data == 'reset') {
                            resetStatement();
                        } else{
                            $('#status_management_data .scroll_col').html(data);
                            $("#status_management").show();
                        }
                        switchOnCollapse('status_list_collapse');
                    } else {
                        alert("Request error.");
                    }
                },"html");
                return false;
            }
        }

        function applyFilter() {
            resetStatement();
            return false;
        }
    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="status_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="status_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>

    </div>
    <!--===Actions ===-->
    </div>
    <!-- /Page Content -->
</div>


<script type="text/javascript">

    $(function(){
        $('.table').on('dblclick', 'tr', function(){
            var orders_status_id = $('.cell_identify', this).val();

            window.location = 'orders_status/edit?' + $('#filterForm').serialize() + '&orders_status_id=' + orders_status_id

        });

        var key = true;
        $(document).ajaxSuccess(function(){
            var url = new URLSearchParams(window.location.search);
            var orders_status_id = url.get('orders_status_id');
            if (orders_status_id) {
                var td = $('input[value="'+orders_status_id+'"]').closest('td');
                if (td.length && key){
                    key = false
                    $('input[value="'+orders_status_id+'"]').closest('td').trigger('click')
                }
            }
        })
    });
</script>