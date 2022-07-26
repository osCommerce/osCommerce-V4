
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<style>
    .trunk{
        padding: 6px 5px 10px 10px;
        margin: 9px 0 5px 1px;
        float:right;
    }
</style>
<!-- /Page Header -->
<!--=== Page Content ===-->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input name="row" id="row_id" value="{$row}" type="hidden">
    <input name="platform_id" id="platform_id" value="{$platform_id}" type="hidden">
    <input name="parent_id" id="parent_id" value="{$parent_id}" type="hidden">
    <input name="item_id" id="item_id" value="{$item_id}" type="hidden">
</form>
{if $isMultiPlatforms}
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
        <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
                <li class="platform-tab {if $platform['id']==$platform_id} active {/if}" data-platform_id="{$platform['id']}"><a onclick="loadModules('seo-delivery-location/list?platform_id={$platform['id']}', {$platform['id']})" data-toggle="tab"><span>{$platform['text']}</span></a></li>
            {/foreach}
        </ul>
    </div>
{/if}
<div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="alert fade in" style="display:none;">
                <i data-dismiss="alert" class="icon-remove close"></i>
                <span id="message_plce"></span>
            </div>
            <div class="widget-content" id="reviews_list_data">
                <div id="list_bread_crumb"></div>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable dataTable catelogue-grid disable_text_highlighting"
                       checkable_list="0" data_ajax="seo-delivery-location/list?platform_id={$platform_id}&parent_id={$parent_id}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->RedirectsTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function resetStatement(reset, resetSearch ) {
            if ( reset !== true ) reset = false;
            var table = $('.table').DataTable();
            if (resetSearch) {
                table.search('');
            }
            table.draw(reset);
            return false;
        }

        function loadModules(url, setPlatformId){
            if ( setPlatformId ) {
                $('#platform_id').val(setPlatformId);
            }
            $('#row_id').val('0');
            $('#parent_id').val('0');
            $('#item_id').val('0');

            var table = $('.table').DataTable();

            table.ajax.url( url ).load();
        }

        function setFilterState() {
            orig = $('#filterForm').serialize();
            var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
            window.history.replaceState({ }, '', url);
        }

        function redirectSave(){
            $.post("seo-delivery-location/submit",
                $('form[name=redirect]').serialize(),
                function (data, status) {
                    if (status == "success") {
                        $('.alert #message_plce').html(data.message);
                        $('.alert').addClass(data.messageType).show();
                        resetStatement();
                    } else {
                        alert("Request error.");
                    }
                }, "json");
            return false;
        }

        function preEditItem( item_id ) {
            $.post("seo-delivery-location/itempreedit", {
                'item_id': item_id,
                {if $isMultiPlatforms}
                'platform_id': $('.platform-tab.active').attr('data-platform_id')
                {else}
                'platform_id': {$default_platform_id}
                {/if}
            }, function (data, status) {
                if (status == "success") {
                    $('#_management_data .scroll_col').html(data);
                    $("#_management").show();
                    // switchOnCollapse('reviews_management_collapse');
                } else {
                    alert("Request error.");
                }
            }, "html");
            return false;
        }

        function onClickEvent(obj, table) {

            var dtable = $(table).DataTable();
            var id = dtable.row('.selected').index();
            $("#row_id").val(id);

            var event_id = $(obj).find('input.cell_identify').val();
            var event_type = $(obj).find('input.cell_type').val();

            $("#item_id").val(event_id);

            setFilterState();

            preEditItem(  event_id );
        }

        function onUnclickEvent(obj, table) {
            return false;
        }
        function onDblClickEvent(obj, table) {

            var event_id = $(obj).find('input.cell_identify').val();
            var event_type = $(obj).find('input.cell_type').val();
            $("#item_id").val(event_id);
            if ( event_type=='folder' ) {
                $("#parent_id").val(event_id);
                setFilterState();
                resetStatement(true, true);
                return;
            }

        }

        function edit(id){
            $.get("seo-delivery-location/location-edit", {
                'item_id': id,
                {if $isMultiPlatforms}
                'platform_id': $('.platform-tab.active').attr('data-platform_id'),
                {else}
                'platform_id': {$default_platform_id},
                {/if}
                'parent_id': $("#parent_id").val(),
            }, function (data, status) {
                if (status == "success") {
                    $('#_management_data .scroll_col').html(data);
                    $("#_management").show();
                } else {
                    alert("Request error.");
                }
            }, "html");
            return false;
        }

        function deleteItem(id){

            bootbox.dialog({
                message: "{$smarty.const.TEXT_DELIVERY_LOCATION_REMOVE_CONFIRM}",
                title: "{$smarty.const.TEXT_DELIVERY_LOCATION_DELETE}",
                buttons: {
                    success: {
                        label: "{$smarty.const.TEXT_BTN_YES}",
                        className: "btn-delete",
                        callback: function(){
                            $.post("seo-delivery-location/delete",
                                {
                                    'item_id' : id ,
                                },
                                function(data, status){
                                    if (status == "success"){
                                        resetStatement();
                                    }
                                },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.TEXT_BTN_NO}",
                        className: "btn-cancel",
                        callback: function () {
                            //console.log("Primary button");
                        }
                    }
                }
            });

            return false;
        }

        function closePopup() {
            $('.popup-box:last').trigger('popup.close');
            $('.popup-box-wrap:last').remove();
        }

        $(document).ready(function(){
            $('.js_create_batch').on('click',function(){
                var params = [];
                params.push({
                    'name' : 'platform_id',
                    'value' : $('#platform_id').val()
                });
                params.push({
                    'name' : 'parent_id',
                    'value' : $('#parent_id').val()
                });
                $('<a href="{Yii::$app->urlManager->createUrl(['seo-delivery-location/batch-create-location'])}"></a>').popUp({
                    data: params
                }).trigger('click');
                return false;
            });
            $('.js_create_item').on('click',function(){
                var requestParams = '&platform_id='+$('#platform_id').val()+'&parent_id='+$('#parent_id').val();
                window.location = '{Yii::$app->urlManager->createUrl(['seo-delivery-location/location-edit','item_id'=>0])}'+requestParams;
                return false;
            });
            $(window).on('reload-frame',function(){
                resetStatement();
            });
            $(document).on('click', '.js-list-load',function(){
                //$(this).attr('data-param-platform_id');
                //$(this).attr('data-param-parent_id');
                //$('#platform_id').val('0');
                $('#row_id').val('0');
                $('#parent_id').val($(this).attr('data-param-parent_id'));
                $('#item_id').val('0');
                resetStatement(true);
                return false;
            });
        })
    </script>
    <div class="row right_column" id="_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
<!--=== reviews management ===-->