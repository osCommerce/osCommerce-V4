<!-- Page Header START -->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- Page Header END -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input name="row" id="row_id" value="{$row}" type="hidden">
    <input name="platform_id" id="platform_id" value="{$platformId}" type="hidden">
    <input name="parent_id" id="parent_id" value="{$parentId}" type="hidden">
    <input name="item_id" id="item_id" value="{$id}" type="hidden">
</form>
{if $isMultiPlatforms}
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
        <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
                <li class="platform-tab {if $platform['id']==$platformId} active {/if}"
                    data-platform_id="{$platform['id']}"><a
                            onclick="loadModules('catalog-pages/list?platform_id={$platform['id']}', {$platform['id']})"
                            data-toggle="tab"><span>{$platform['text']}</span></a></li>
            {/foreach}
        </ul>
    </div>
{/if}
<div class="order-wrap">
    <!--Catalog Pages List  START-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content" id="groups_list_data">
                <div id="list_bread_crumb"></div>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable catelogue-grid table-catalog-pages"
                       checkable_list="0,1" data_ajax="{Yii::$app->urlManager->createUrl(['catalog-pages/list','platform_id'=>$platformId])}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->groupsTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--Catalog Pages List  END-->
    <!--Catalog Pages Service START-->
    <div class="row" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="catalog-pages-service">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--Catalog Pages Service END-->
    <script type="text/javascript">

        function resetStatement(reset, resetSearch) {
            if (reset !== true) reset = false;
            var table = $('.table').DataTable();
            if (resetSearch) {
                table.search('');
            }
            $('#catalog-pages-service .scroll_col').html('');
            table.draw(reset);
            return false;
        }

        function applyFilter() {
            $("#row_id").val(0);
            resetStatement();
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

        function switchStatement(id, status) {
            $.post("{Yii::$app->urlManager->createUrl('catalog-pages/change-status')}", {
                'id': id,
                'status': status
            }, function (data, status) {
                if (status === "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            }, "html");
        }


        function preEditItem(item_id) {
            $.post("{Yii::$app->urlManager->createUrl('catalog-pages/view')}", {
                'id': item_id
            }, function (data, status) {
                if (status === "success") {
                    $('#catalog-pages-service .scroll_col').html(data);
                    $('.js-open-tree-popup').popUp();
                } else {
                    alert("Request error.");
                }
            }, "html");
            return false;
        }

        function deleteItemConfirm(id,confirm) {
            confirm = confirm || false;
            $.post("{Yii::$app->urlManager->createUrl('catalog-pages/delete')}", {
                'id': id,
                'confirm': confirm
            }, function (data, status) {
                if (status === "success") {
                    if(data.reload){
                        resetStatement();
                    }else{
                        $('#catalog-pages-service .scroll_col').html(data.data);
                    }
                } else {
                    alert("Request error.");
                }
            }, "json");
            return false;
        }

        $(document).ready(function () {
            $(".js-table-sortable.datatable tbody").sortable({
                axis: 'y',
                update: function (event, ui) {
                    $(this).find('[role="row"]').each(function () {
                        if (this.id) return;
                        var cell_ident = $(this).find('.cell_identify');
                        var cell_type = $(this).find('.cell_type');
                        if (cell_ident.length > 0 && cell_type.length > 0) {
                            this.id = cell_type.val() + '_' + cell_ident.val();
                        }
                    });
                    var post_data = [];
                    $(this).find('[role="row"]').each(function () {
                        var spl = this.id.indexOf('_');
                        if (spl === -1) return;
                        post_data.push({
                            name: this.id.substring(0, spl) + '[]', value: this.id.substring(spl + 1)
                        });
                    });
                    var $dropped = $(ui.item);
                    post_data.push({
                        name: 'sort_' + $dropped.find('.cell_type').val(),
                        value: $dropped.find('.cell_identify').val()
                    });
                    post_data.push({
                        name: 'parent_id',
                        value: $('#parent_id').val()
                    });
                    post_data.push({
                        name: 'platform_id',
                        value: $('#platform_id').val()
                    });
                    $.post("{Yii::$app->urlManager->createUrl('catalog-pages/sort')}", post_data, function (data, status) {
                        if (status === "success") {
                            resetStatement();
                        } else {
                            alert("Request error.");
                        }
                    }, "html");
                },
                handle: ".handle"
            }).disableSelection();



            $(window).resize(function () {
                setTimeout(function () {
                    var height_box = $('.order-wrap').height();
                    $('#order_management .widget').css('min-height', height_box);
                }, 800);
            })
            $(window).resize();

            $('body').on('click','#createPage',function(e){
                e.preventDefault();
                var requestParams = '&platform_id='+$('#platform_id').val()+'&parent_id='+$('#item_id').val();
                window.location = '{Yii::$app->urlManager->createUrl(['catalog-pages/edit','id' => 0])}'+requestParams;
                return false;
            });

        });

        function setFilterState() {
            orig = $('#filterForm').serialize();
            var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '');
            window.history.replaceState({

            }, '', url);
        }

        function onClickEvent(obj, table) {
            var dtable = $(table).DataTable();
            var id = dtable.row('.selected').index();
            $("#row_id").val(id);
            setFilterState();

            $(".check_on_off").bootstrapSwitch(
                {
                    onSwitchChange: function (element, arguments) {
                        switchStatement(element.target.value, arguments);
                        return true;
                    },
                    onText: "{$smarty.const.SW_ON}",
                    offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                }
            );

            var event_id = $(obj).find('input.cell_identify').val();
            preEditItem(event_id);
        }

        function onUnclickEvent(obj, table) {
            var event_id = $(obj).find('input.cell_identify').val();
        }
        function onDblClickEvent(obj, table) {
            var event_id = $(obj).find('input.cell_identify').val();
            var event_parent_id = $(obj).find('input.cell_identify_parent').val();
            var event_type = $(obj).find('input.cell_type').val();
            $("#item_id").val(event_id);
            if ( event_type=='folder' ) {
                $("#parent_id").val(event_parent_id);
                setFilterState();
                resetStatement(true, true);
                return;
            }

        }
    </script>
</div>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet"
      type="text/css"/>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
