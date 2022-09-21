{use class="common\helpers\Html"}
<div class="widget2 box box-no-shadow">
              <div class="widget-header widget-header-theme"><h4>{$smarty.const.BOX_CATALOG_WAREHOUSES}</h4></div>
              <div class="widget-content">
                  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                        <tr>
                            <th>{$smarty.const.TEXT_WAREHOUSE}</th>
                            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $warehouses as $warehouse}
                        <tr>
                            <td>{$warehouse['text']}</td>
                            <td>
                                <input type="checkbox" name="warehouse_status[{$warehouse['id']}]" {if $warehouse['status']} checked{/if} class="js_check_status" value="1"/>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                    <div class="countries_popup popup-box-wrap-page hide_popup warehouses-table">
                        <div class="around-pop-up-page"></div>
                        <div class="popup-box-page">
                            <div class="pop-up-close-page"></div>
                            <div class="pop-up-content-page">
                                <div class="popup-heading">{$smarty.const.TEXT_SET_UP} {$smarty.const.BOX_WAREHOUSES_PRIORITY}</div>
                                <div class="popup-content">
                                    <div class="priorityList">
                                    {foreach $warehouse_priorities as $warehouse_priority}
                                        {assign var="rowId" value="{$warehouse_priority->handler_class}"}
                                    <div class="widget box box-no-shadow js-warehouse-priority" style="margin-bottom: 10px;" id="{$warehouse_priority->handler_class}">
                                        {Html::hiddenInput('priority['|cat:$rowId|cat:'][id]',$warehouse_priority->id)}
                                        {Html::hiddenInput('priority['|cat:$rowId|cat:'][sort_order]',$warehouse_priority->sort_order,['class'=>'js-sort-order'])}
                                        <div class="widget-header">
                                            <div class="row" style="flex-grow: 1">
                                                <div class="col-md-1">
                                                    <span class="handle"><i class="icon-hand-paper-o"></i></span>
                                                </div>
                                                <div class="col-md-7">
                                                    <h4>{$warehouse_priority->title}</h4>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <div style="padding-right: 40px;display: inline-block">
                                                    {$smarty.const.TEXT_STATUS}
                                                        <input type="checkbox" name="priority[{$rowId}][handler_status]" {if $warehouse_priority->handler_status} checked{/if} class="js_handler_status" value="1"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="toolbar no-padding">
                                                <div class="btn-group">
                                                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="widget-content after">
                                            <table class="table table-bordered js-weight-table" data-row-counter="{$warehouse_priority->getWeightTable()|count}">
                                                <thead>
                                                <tr>
                                                    <th>{$warehouse_priority->getParamColumnName()}</th>
                                                    <th>{$smarty.const.TEXT_WEIGHT_COEFFICIENT}</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                {foreach $warehouse_priority->getWeightTable() as $rowIdx=>$weightRow}
                                                <tr>
                                                    <td>{Html::textInput('priority['|cat:$rowId|cat:'][weightTable]['|cat:$rowIdx|cat:'][param]', $weightRow['param'],['class'=>'form-control'])}</td>
                                                    <td>{Html::textInput('priority['|cat:$rowId|cat:'][weightTable]['|cat:$rowIdx|cat:'][weight]', $weightRow['weight'],['class'=>'form-control'])}</td>
                                                    <td align="center"><i class="icon-trash color-alert js-remove-weight-row"></i></td>
                                                </tr>
                                                {/foreach}
                                                </tbody>
                                                <tfoot style="display: none">
                                                <tr>
                                                    <td>{Html::textInput('priority[%%rowId%%][weightTable][%%rowCounter%%][param]','',['class'=>'form-control'])}</td>
                                                    <td>{Html::textInput('priority[%%rowId%%][weightTable][%%rowCounter%%][weight]','',['class'=>'form-control'])}</td>
                                                    <td align="center"><i class="icon-trash color-alert js-remove-weight-row"></i></td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                            <div class="btn-bar" style="padding: 0">
                                                <div class="pull-left">
                                                    {$warehouse_priority->getNote()}
                                                </div>
                                                <div class="pull-right">
                                                    <button type="button" class="btn btn-add-more js-add-weight-row">{$smarty.const.TEXT_ADD_MORE}</button>
                                                </div>
                                            </div>
                                            {*$warehouse_priority->configView*}
                                        </div>
                                    </div>
                                    {/foreach}
                                    </div>
                                </div>
                                <div class="btn-bar">
                                    <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                                    <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="btn-small-bar after">
                    <div class="btn-right"><a class="btn popup_lang" href="#warehouses-table" data-class="warehouses-table">{$smarty.const.BOX_WAREHOUSES_PRIORITY}</a></div>
                </div>
              </div>
          </div>
<script>
$(document).ready(function(){
    let warehousesTable = $('.warehouses-table').clone();
    $('.popup_lang').on('click', function(e){
        e.preventDefault();
        $('.warehouses-table').removeClass('hide_popup');
    })
    applyPopUp()
    function applyPopUp() {
        $('.warehouses-table .apply-popup').on('click', function (e) {
            e.preventDefault();
            $('.warehouses-table').addClass('hide_popup')
        })
        $('.warehouses-table .cancel-popup, .warehouses-table .pop-up-close-page').on('click', function (e) {
            e.preventDefault();
            $('.warehouses-table').replaceWith(warehousesTable.clone())
            applyPopUp()
        })
        $('.js_handler_status').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
        $('.priorityList').on('update_sort_order', function (event, manualUpdate) {
            $('.js-warehouse-priority').each(function (idx) {
                $(this).find('.js-sort-order').val('' + idx);
            });
        });
        $('.priorityList').sortable({
            axis: 'y',
            update: function (event, ui) {
                $('.priorityList').trigger('update_sort_order', [true]);
            }
        });
        $('.priorityList').disableSelection();

        $('.js-weight-table').on('add_new_row', function () {
            var $table = $(this);
            var rowStr = $('tfoot', $table).html();
            rowStr = rowStr.replace(/%%rowId%%/g, $table.parents('.js-warehouse-priority').attr('id'));
            var rowCounter = parseInt($table.data('row-counter'), 10);
            rowStr = rowStr.replace(/%%rowCounter%%/g, rowCounter);
            $table.data('row-counter', rowCounter + 1);
            $('tbody', $table).append(rowStr);
        });

        $('.js-weight-table').each(function () {
            var $table = $(this);
            if ($table.find('tbody tr').length == 0) {
                $table.trigger('add_new_row');
            }
        });

        $('.js-warehouse-priority').on('click', '.js-add-weight-row', function (event) {
            $(event.delegateTarget).find('.js-weight-table').trigger('add_new_row')
        });
        $('.js-warehouse-priority').on('click', '.js-remove-weight-row', function (event) {
            $(event.target).parents('tr').remove();
        });
    }
});
</script>