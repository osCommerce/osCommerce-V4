<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>

<!-- /Page Header -->
<div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">
        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <div class="/*filter-box*/ filter-box-cus {if $isMultiPlatform}filter-box-pl{/if}">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <table style="display: inline-block; vertical-align: top; width:24%;">
                                <tr>
                                    <td align="left">
                                        {if $isMultiPlatform}
                                            <fieldset style="display:inline-grid;">
                                                <legend>{$smarty.const.TEXT_COMMON_PLATFORM_FILTER}</legend>
                                                <div class="f_td f_td_radio ftd_block">
                                                    <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value=""> {$smarty.const.TEXT_COMMON_PLATFORM_FILTER_ALL}</label></div>
                                                    {foreach $platforms as $platform}
                                                        <div><label class="radio_label"><input type="checkbox" name="platform[]" class="js_platform_checkboxes uniform" value="{$platform['id']}" {if in_array($platform['id'], $app->controller->view->filters->platform)} checked="checked"{/if}> {$platform['text']}</label></div>
                                                    {/foreach}
                                                </div>
                                            </fieldset>
                                        {/if}
                                        {*<fieldset style="display:inline-grid;">
                                            <legend>{$smarty.const.TEXT_DATE_RANGE}</legend>
                                            <div class="f_td f_td_radio ftd_block">
                                                <div><label>{$smarty.const.TEXT_FROM}<input type="text" name="start_date" class="form-control datepicker" value="{$app->controller->start_date}"></label></div>
                                                <div><label>{$smarty.const.TEXT_TO}<input type="text" name="end_date" class="form-control datepicker" value="{$app->controller->end_date}"></label></div>
                                            </div>
                                        </fieldset>*}
                                    </td>
                                </tr>
                            </table>

                            {foreach $app->controller->view->filters->by as $label => $Items}
                                <table style="display: inline-block; vertical-align: top; width:24%">
                                    <tr>
                                        <td align="left">
                                            <fieldset style="display:inline-grid;">
                                                {if $label}<legend>{$label}</legend>{/if}
                                                {foreach $Items as $item}
                                                    <label>{$item['label']}:</label>
                                                    {if $item['type'] == 'text'}
                                                        <input name="{$item['name']}" id="select_{$item['name']}" type="text" value="{$item['value']}" class="form-control"><br/>
                                                    {else if $item['type'] == 'dropdown'}
                                                        {tep_draw_pull_down_menu($item['name'], $item['value'], $item['selected'], ' class="form-control" id="select_'|cat:$item['name']|cat:'"')}<br/>
                                                    {/if}
                                                {/foreach}
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">
                            <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>&nbsp;
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
            </div>
        </form>
    </div>
</div>
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="row">
                    <div class="col-md-12">

                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>

                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid table-no-search" checkable_list="0,1,3,4,5" data_ajax="report-products-notify/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->reportTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>


                <p class="btn-wr">
                    <a style="display:none;" href="javascript:void(0);" ></a>
                    <button class="btn btn-primary" onClick="_export();">{$smarty.const.TEXT_EXPORT}</button>
                </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        var fTable;

        function resetFilter(){
            $.each(document.getElementById('filterForm').elements, function(i, e) {
                if ($(e).is('input')) {
                    $(e).val('');
                } else if ($(e).is('select')) {
                    if ($(e).attr('name') == 'notified') {
                        $(e).val('2'); // Not notified
                    } else {
                        $(e).val('') || $(e).val(0);
                    }
                }
            })
            applyFilter();
        }
        function setFilterState(only_query) {
            orig = $('#filterForm').serialize();
            var query = orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
            if (only_query){
                return query;
            } else {
                var url = window.location.origin + window.location.pathname + '?' + query;
                window.history.replaceState({ }, '', url);
            }
        }

        function resetStatement() {

            setFilterState();
            fTable.fnDraw(false);
            return false;
        }
        function onClickEvent(obj, table) {
            fTable = table;
            var rows = fTable.fnGetNodes();
            var toral_row = rows[rows.length-1];
            $(toral_row).addClass('orange');
        }

        function onUnclickEvent(obj, table) {
        }

        function applyFilter() {
            resetStatement();
            return false;
        }

        function getTableSelectedCount(){
            return 0;
        }

        var currentItem;
        $(document).ready(function(){
            $( ".datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true,
                showOtherMonths:true,
                autoSize: false,
                dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
                onSelect: function (e) {
                    if ($(this).val().length > 0) {
                        $(this).siblings('span').addClass('active_options');
                    }else{
                        $(this).siblings('span').removeClass('active_options');
                    }
                }
            });


            var $platforms = $('.js_platform_checkboxes');

            $('.js_platform_checkboxes[value=""]').click(function(){
                if ($(this).parent().hasClass('checked')){
                    $('.js_platform_checkboxes').filter('[value!=""]').each(function() {
                        $(this).parent().addClass('checked');
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.js_platform_checkboxes').filter('[value!=""]').each(function() {
                        $(this).parent().removeClass('checked');
                        $(this).prop('checked', false);
                    });
                }
            })

            var check_platform_checkboxes = function(){
                if (!$platforms.size()) return;
                var checked_all = true;
                $platforms.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });

                if (checked_all){
                    $platforms.filter('[value=""]').parent().addClass('checked');
                    $platforms[0].checked = true;
                } else {
                    $platforms.filter('[value=""]').parent().removeClass('checked');
                    $platforms[0].checked = false;
                }
            };

            check_platform_checkboxes();
        });

        function _export(){
            var form = $('#filterForm')[0];
            form.setAttribute('action', 'report-products-notify/export?' + setFilterState(true));
            form.submit();
        }

    </script>
    <!-- /Page Content -->
</div>