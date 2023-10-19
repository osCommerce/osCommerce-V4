{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}

<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--===banners list===-->
<div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content fillBan">
        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <input type="hidden" name="group_id" value="{$group_id}"/>
            <input type="hidden" name="platform_id" value="{$platform_id}"/>
            <input type="hidden" name="row_id" value="{$row_id}" id="row_id"/>

            <div class="banner-filter">
                <div class="row">
                    <div class="col-5">

                        <h4 class="show-empty-groups">
                            {$smarty.const.SHOW_EMPTY_GROUPS} <input type="checkbox" name="empty_groups" class="empty-groups" />
                        </h4>

                        <div class="show-banner-columns">
                            <h4>{$smarty.const.SHOW_COLUMNS}</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_batch" value=""> {$smarty.const.BATCH_CHECKBOXES}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_sort" value=""> {$smarty.const.SORTING_HANDLE}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_image" value=""> {$smarty.const.TAB_IMAGES}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_title" value=""> {$smarty.const.TEXT_TITLE}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_file" value=""> {$smarty.const.FILE_NAME}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_text" value=""> {$smarty.const.TEXT_TEXT}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_platform" value=""> {$smarty.const.TEXT_PLATFORM}
                                        </label>
                                    </div>
                                    <div class="m-b-1">
                                        <label class="radio_label">
                                            <input type="checkbox" name="show_status" value=""> {$smarty.const.TABLE_HEADING_STATUS}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-7">
                        <h4>Search by</h4>
                        <div class="row">
                            <div class="col-6">
                                <div class="m-b-2">
                                    <input type="text" name="search_title" value="{$search_title}" class="form-control" placeholder="{$smarty.const.TEXT_TITLE}"/>
                                </div>
                                <div class="m-b-2">
                                    <input type="text" name="search_file" value="{$search_file}" class="form-control" placeholder="{$smarty.const.FILE_NAME}"/>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="m-b-2">
                                    <input type="text" name="search_text" value="{$search_text}" class="form-control" placeholder="{$smarty.const.TEXT_TEXT}"/>
                                </div>
                                <div class="select-status">
                                    {$smarty.const.ENTRY_STATUS}:
                                    <label>
                                        <input type="radio" name="search_status" value=""{if $search_status == ''} checked{/if}/>
                                        {$smarty.const.TEXT_ALL}
                                    </label>
                                    <label>
                                        <input type="radio" name="search_status" value="on"{if $search_status == 'on'} checked{/if}/>
                                        {$smarty.const.TEXT_ON}
                                    </label>
                                    <label>
                                        <input type="radio" name="search_status" value="off"{if $search_status == 'off'} checked{/if}/>
                                        {$smarty.const.TEXT_OFF}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="order-wrap">
    <div class="row container-box-list">
        <div class="col-md-12">
            <div class="widget-content widget_relative">

                <div class="data-table-top-left breadcrumbs">
                    <div class="top">{$smarty.const.TEXT_TOP}</div>
                    <div class="platform">{$smarty.const.TABLE_HEADING_PLATFORM}</div>
                    <div class="group">{$smarty.const.TABLE_HEADING_GROUPS}</div>
                </div>

                <table class="table table-selectable table-striped table-hover table-responsive table-bordered datatable double-grid table-banner_manager" data_ajax="banner_manager/list">
                    <thead>
                    <tr>
                        <th class="batch-heading-cell"><input type="checkbox" name="batch"/></th>
                        <th class="sort-heading-cell"></th>
                        <th class="image-heading-cell">{$smarty.const.TAB_IMAGES}</th>
                        <th class="title-heading-cell">{$smarty.const.TEXT_TITLE}</th>
                        <th class="group-heading-cell">{$smarty.const.TABLE_HEADING_GROUPS}</th>
                        <th class="file-heading-cell">{$smarty.const.FILE_NAME}</th>
                        <th class="text-heading-cell">{$smarty.const.TEXT_TEXT}</th>
                        <th class="platform-heading-cell">{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                        <th class="platform-heading-cell">{$smarty.const.TABLE_HEADING_PLATFORM}{*$smarty.const.TABLE_HEAD_PLATFORM_BANNER_ASSIGN*}</th>
                        <th class="status-heading-cell">{$smarty.const.TABLE_HEADING_STATUS}</th>
                        <th class="count-heading-cell">{$smarty.const.NUMBER_OF_BANNERS}</th>
                    </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>

    <!--===  banners management ===-->
    <div class="row right_column" id="banners_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="banners_management_data">
                <div class="scroll_col"></div>

                <div class="batchCol" style="display: none">
                    <div class="or_box_head">{$smarty.const.TEXT_BATCH_ACTIONS}</div>
                    <div class="after btn-wr-top1 js-batch-buttons" style="margin: 4px;">
                        <div>
                            <span class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</span>
                            <span class="btn btn-on-sel">{$smarty.const.TEXT_ON_SELECTED}</span>
                            <span class="btn btn-off-sel">{$smarty.const.TEXT_OFF_SELECTED}</span>
                            {*<span class="btn btn-move">Change groups</span>*}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!--=== banners management ===-->
</div>


<script>
    {if $platform_name && $platform_id}
        let platforms = { '{$platform_id}': '{$platform_name}' };
    {else}
        let platforms = { };
    {/if}
    {if $group_name && $group_id}
        let groups = { '{$group_id}': '{$group_name}' };
    {else}
        let groups = { };
    {/if}

    function viewModule(id, name){
        const platform_id = $('input[name="platform_id"]').val();
        const row_id = $('input[name="platform_id"]').val();
        $.get('banner_manager/view', { id, name, platform_id, row_id }, function(data, status){
            if (status == "success") {
                $('#banners_management_data .scroll_col').html(data);
                $("#banners_management").show();
                heightColumn();
            } else {
                alert("Request error.");
            }
        });
    }

    function resetStatementStatus() {
        var table = $('.table').DataTable();
        table.draw(false);
        return false;
    }


    function onClickEvent (obj, table){
        $('#row_id').val(table.find(obj).index());

        const id = $(obj).find('div[data-id]').data('id');
        const name = $(obj).find('div[data-name]').data('name');
        viewModule(id, name);

        $('.table tbody').sortable({
            handle: '.sort-cell',
            axis: 'y',
            update: function( event, ui ) {
                const ids = [];
                $('.sort-cell').each(function () {
                    ids.push($(this).data('id'))
                });

                $.post('banner_manager/sort', { ids })
            }
        });

        $('.table').removeClass('banners').removeClass('groups').removeClass('platforms');
        if (
            +$('input[name="platform_id"]').val() &&
            +$('input[name="group_id"]').val() ||
            $('input[name="search_title"]').val() ||
            $('input[name="search_text"]').val() ||
            $('input[name="search_file"]').val() ||
            $('.dataTables_filter input[type="search"]').val()
        ) {
            $('.table').addClass('banners');
            $('.show-banner-columns').show();
            $('.btn-new-group').hide();
            $('.btn-new-banner').show();
            $('.select-status').show();
            $('.show-empty-groups').hide();
        } else if (+$('input[name="platform_id"]').val()) {
            $('.table').addClass('groups');
            $('.show-banner-columns').hide();
            $('.btn-new-group').show();
            $('.btn-new-banner').hide();
            $('.select-status').hide();
            $('.show-empty-groups').show();
        } else {
            $('.table').addClass('platforms');
            $('.show-banner-columns').hide();
            $('.btn-new-group').hide();
            $('.btn-new-banner').hide();
            $('.select-status').hide();
            $('.show-empty-groups').hide();
        }

        setBreadcrumbs();
        setFilterState();
    }

    function setBreadcrumbs() {
        const $top = $(`<div><span class="name">{$smarty.const.TEXT_TOP}</span></div>`);
        $('.breadcrumbs').html('').append($top)
        $top.on('click', function(){
            {if common\classes\platform::isMulti()}
            $('input[name="platform_id"]').val('');
            {else}
            $('input[name="platform_id"]').val('{common\classes\platform::defaultId()}');
            {/if}
            $('input[name="group_id"]').val('');
            resetStatement()
        })

        if (+$('input[name="platform_id"]').val()) {
            const $platform = $(`
                <div{if !common\classes\platform::isMulti()} style="display: none;"{/if}>
                    <span class="title">{$smarty.const.TABLE_HEADING_PLATFORM}</span><span class="colon">:</span>
                    <span class="name">${ platforms[''+$('input[name="platform_id"]').val()] }</span>
                </div>`);
            $('.breadcrumbs').append($platform)
            $platform.on('click', function(){
                $('input[name="group_id"]').val('');
                resetStatement()
            })
        }
        if (+$('input[name="group_id"]').val()) {
            const $group = $(`
                <div>
                    <span class="title">Group</span><span class="colon">:</span>
                    <span class="name">${ groups[''+$('input[name="group_id"]').val()] }</span>
                </div>`);
            $('.breadcrumbs').append($group)
        }
    }

    function onUnclickEvent(obj, table){
    }

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
        window.history.replaceState({ }, '', url);

        let newBannerUrl = 'banner_manager/banneredit?group_id=' + $('input[name="group_id"]').val() + '&platform_id=' + $('input[name="platform_id"]').val();
        $('.btn-new-banner').attr('href', newBannerUrl)
        let newGroupUrl = 'banner_manager/banner-groups-edit?platform_id=' + $('input[name="platform_id"]').val();
        $('.btn-new-group').attr('href', newGroupUrl)
    }

    function resetStatement() {
        setFilterState();
        $(".order-wrap").show();

        $('#banner_management_data .scroll_col').html('');
        $('#banner_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        //$(window).scrollTop(0);

        return false;
    }

    function resetFilter() {
        $('select[name="filter_by"]').val('');
        $("#row_id").val(0);
        resetStatement();
        return false;
    }

    function applyFilter() {
        var $platforms = $('.js_platform_checkboxes');
        if ( $platforms.length>0 ) {
            var http_method = false;
            $platforms.filter('[data-checked]').each(function(){
                if ( this.checked != ($(this).attr('data-checked')=='true') ) {
                    http_method = true;
                }
            });
            if ( http_method ) return true;
        }
        resetStatement();
        return false;
    }

    $(document).ready(function(){
        var $platforms = $('.js_platform_checkboxes');
        var check_platform_checkboxes = function(){
            var checked_all = true;
            $platforms.not('[value=""]').each(function () {
                if (!this.checked) checked_all = false;
            });
            $platforms.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        };
        check_platform_checkboxes();
        $platforms.on('click',function(){
            var self = this;
            if (this.value=='') {
                $platforms.each(function(){
                    this.checked = self.checked;
                });
            }else{
                var checked_all = this.checked;
                if ( checked_all ) {
                    $platforms.not('[value=""]').each(function () {
                        if (!this.checked) checked_all = false;
                    });
                }
                $platforms.filter('[value=""]').each(function() {
                    this.checked = checked_all
                });
            }
        });
    })

    $(function(){

        const $allBatch = $('.batch-heading-cell input');
        $('.table').on('draw.dt', function() {
            const $inputs = $('.batch-cell input');
            $inputs.on('change', checkBatch);
            checkBatch();

            function checkBatch(){
                const checkedLength = $('.batch-cell input:checked').length;
                if (checkedLength) {
                    $('.batchCol').show();
                    $('.scroll_col').hide();
                } else {
                    $('.batchCol').hide();
                    $('.scroll_col').show();
                }
                if (checkedLength == $inputs.length) {
                    $allBatch.prop('checked', true)
                } else {
                    $allBatch.prop('checked', false)
                }
            }
        });
        $allBatch.on('change', function () {
            const $inputs = $('.batch-cell input');
            if ($(this).prop('checked')) {
                $inputs.prop('checked', true);
                $('.batchCol').show();
                $('.scroll_col').hide();
            } else {
                $inputs.prop('checked', false);
                $('.batchCol').hide();
                $('.scroll_col').show();
            }
        })

        const $columnInputs = $('.show-banner-columns input');

        const bannerTable = window.localStorage.getItem('bannerTable1');
        let columns = [];
        let _columns = [];
        if (bannerTable) {
            _columns = JSON.parse(bannerTable);
        } else {
            _columns = [
                { name: "show_file", value: false },
                { name: "show_text", value: false },
                { name: "show_platform", value: false }
            ];
        }

        $columnInputs.each(function(){
            const name = $(this).attr('name');
            const item = _columns.find(i => i.name === name) || { name, value: true };
            columns.push(item)
        });

        columns.forEach(function(item){
            $(`.show-banner-columns input[name="${ item.name }"]`).prop('checked', item.value);
        });

        $columnInputs.on('change', function(){
            const name = $(this).attr('name');
            const value = $(this).prop('checked');
            columns = columns.map(i => i.name === name ? { name, value } : i);

            window.localStorage.setItem('bannerTable1', JSON.stringify(columns));
            showHideColumns()
        });

        $('.table').on('draw.dt', showHideColumns);

        $('body').on('dblclick', '.double-click', function(){
            if ($(this).data('name') == 'platform_id') {
                platforms[''+$(this).data('id')] = $(this).text();
            }
            if ($(this).data('name') == 'group_id') {
                groups[''+$(this).data('id')] = $(this).text();
            }
            if ($(this).data('name') == 'banners_id') {
                const group_id = $('#filterForm input[name="group_id"]').val();
                const platform_id = $('#filterForm input[name="platform_id"]').val();
                const row_id = $('#filterForm input[name="row_id"]').val();
                window.location.href = `banner_manager/banneredit?banners_id=${ $(this).data('id') }&group_id=${ group_id }&platform_id=${ platform_id }&row_id=${ row_id }`;
            }
            $(`#filterForm input[name="${ $(this).data('name') }"]`).val($(this).data('id'));
            applyFilter()
        })

        function showHideColumns() {
            columns.forEach(function(item){
                const name = item.name.replace('show_', '');
                if (item.value || $('.table').hasClass('platforms') || $('.table').hasClass('groups')) {
                    $(`.${ name }-heading-cell`).show()
                    $(`.${ name }-cell`).parent().show()
                } else {
                    $(`.${ name }-heading-cell`).hide()
                    $(`.${ name }-cell`).parent().hide()
                }
            })
        }

        $('.js-batch-buttons .btn-del').on('click', function () {
            const ids = [];
            $('.batch-cell input:checked').each(function(){
                ids.push($(this).val())

            })
            const delete_image = $('input[name="delete_image"]').prop('checked');
            $.get('banner_manager/delete-confirm', { bID: ids, delete_image }, function(data, status){
                if (status == "success") {
                    $('#banners_management_data .scroll_col').html(data);

                    $('.batchCol').hide();
                    $('.scroll_col').show();
                } else {
                    alert("Request error.");
                }
            });
        });

        $('.js-batch-buttons .btn-on-sel').on('click', function () {
            const ids = [];
            $('.batch-cell input:checked').each(function(){
                ids.push($(this).val())
            })
            switchStatement(ids, true)
        });

        $('.js-batch-buttons .btn-off-sel').on('click', function () {
            const ids = [];
            $('.batch-cell input:checked').each(function(){
                ids.push($(this).val())
            })
            switchStatement(ids, false)
        });



        function switchStatement(ids, status, platform = false) {
            $.post("banner_manager/switch-status" + (platform ? '-platform' : ''), { ids, status }, function(data, status){
                if (status == "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            });
        }

        $('.table').on('draw.dt', function() {
            $(".status-cell .check_on_off").bootstrapSwitch({
                onSwitchChange: function (element, arguments) {
                    switchStatement([element.target.value], arguments);
                    return true;
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            });
            $(".platforms-cell-checkbox .check_on_off").bootstrapSwitch({
                onSwitchChange: function (element, arguments) {
                    switchStatement([element.target.value], arguments, true);
                    return true;
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            });
        });

        if (!window.localStorage.getItem('bannersHideEmptyGroups')) {
            $('.empty-groups').prop('checked', true);
        }
        $('.empty-groups').bootstrapSwitch({
            onSwitchChange: function (element, arguments) {
                if (arguments) {
                    window.localStorage.removeItem('bannersHideEmptyGroups')
                } else {
                    window.localStorage.setItem('bannersHideEmptyGroups', '1')
                }
                resetStatement()
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });

        $('input[name="search_title"]').on('keyup change', function () {
            $('input[name="show_title"]').prop('checked', true).trigger('change')
            resetStatement()
        })
        $('input[name="search_file"]').on('keyup change', function () {
            $('input[name="show_file"]').prop('checked', true).trigger('change')
            resetStatement()
        })
        $('input[name="search_text"]').on('keyup change', function () {
            $('input[name="show_text"]').prop('checked', true).trigger('change')
            resetStatement()
        })
        $('input[name="search_status"]').on('change', function () {
            $('input[name="show_status"]').prop('checked', true).trigger('change')
            resetStatement()
        })
    })

</script>
