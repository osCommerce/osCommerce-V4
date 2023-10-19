{use class="common\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div class="widget box box-wrapp-blue box-wrapp-blue2 filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        <div class="filter-box filter-box-cus">
          {Html::beginForm('filterForm', 'get', ['id'=>'filterForm', 'name'=>'filter-form'])}

            <div class="row m-b-2">
                <div class="col-sm-3 align-right">
                    <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                </div>
                <div class="col-sm-4">
                    <select class="form-control" name="by">
                        {foreach $app->controller->view->filters->by as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-sm-5">
                    {Html::textInput('search', $app->controller->view->filters->search)}
                </div>
            </div>

            <div class="row m-b-2">
                <div class="col-sm-3 align-right">
                    <label for="inactive">{$smarty.const.TEXT_FEATURED_TYPES}:</label>
                </div>
                <div class="col-sm-4">
                    {Html::dropDownList('featured_type_id', $selected_type_id, $app->controller->view->types, ['class'=>"form-control"])}
                </div>
                <div class="col-sm-2 align-right">
                    <label for="inactive">{$smarty.const.TEXT_INACTIVE}:</label>
                </div>
                <div class="col-sm-3">
                    {Html::checkbox('inactive', $app->controller->view->filters->inactive)}
                </div>
            </div>

            <div class="row m-b-2">
                <div class="col-sm-3 align-right">
                    <label>{$smarty.const.TEXT_DATE}:</label>
                </div>
                <div class="col-sm-1 align-right">
                    <label>{$smarty.const.TEXT_FROM}</label>
                </div>
                <div class="col-sm-2">
                    {Html::textInput('dfrom', $app->controller->view->filters->dfrom, ['class' => "datepicker form-control form-control-small", 'autocomplete' => 'off'])}
                </div>
                <div class="col-sm-1 align-right">
                    <label>{$smarty.const.TEXT_TO}</label>
                </div>
                <div class="col-sm-2">
                    {Html::textInput('dto', $app->controller->view->filters->dto, ['class' => "datepicker form-control form-control-small", 'autocomplete' => 'off'])}
                </div>
                <div class="col-sm-3">
                    {Html::dropDownList('date', $app->controller->view->filters->date, $app->controller->view->filters->dateOptions, ['class' => "form-control form-control-small"])}
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 align-right">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                    <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                </div>
            </div>

              <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
          {Html::endForm()}
        </div>
    </div>
</div>
<!--===featured list===-->
<div class="order-wrap">
<input type="hidden" id="row_id">
<div class="row order-box-list" id="featured_list">
    <div class="col-md-12">
        <div class="btn-wr after btn-wr-top data-table-top-left">
            <div>
                <div class="p-l-1 switchable disable-btn">
                    <a href="javascript:void(0)" onclick="deleteSelectedFeatured();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                </div>
                <div id>
                    <button id = "loadCurrentSort" class="btn btn-undo">{$smarty.const.TEXT_LOAD_SORT}</button>
                    <button id = "saveCurrentSort" class="btn btn-save">{$smarty.const.TEXT_SAVE_SORT}</button>
                </div>
            </div>
        </div>

            <div class="widget-content" id="featured_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-sortable datatable double-grid table-statuses table-featured"
                       checkable_list="{$app->controller->view->sortColumns}" data_ajax="featured/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->featuredTable as $tableItem}
                            <th class="{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} checkbox-column sorting_disabled"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} hidden-xs{/if}">{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--===/featured list===-->

<script type="text/javascript">
    $(document).ready(function(){
        var dt = $('.datatable').DataTable();
        // init default sorting
        dt.column(0).visible(false);
        dt.column(0).order('asc').draw();

        let loadSortBtn = $('#loadCurrentSort');
        let saveSortBtn = $('#saveCurrentSort');

        function switchBtnState(mode) {
            if (mode === false) {
                loadSortBtn.addClass('disable-btn');
                saveSortBtn.addClass('disable-btn');
            } else {
                loadSortBtn.removeClass('disable-btn');
                saveSortBtn.removeClass('disable-btn');
            }
        }

        switchBtnState(false);

        loadSortBtn.on('click', function () {
            dt.column(0).order('asc').draw();
            switchBtnState(false);
        });
        $( ".table tbody" ).sortable({
            axis: 'y',
            update: function( event, ui ) {
                var post_data = { };
                let sort_data = { };
                let current = $('.cell_id', ui.item).val();

                sort_data.current = current;
                let items = event.target.childNodes;
                items.forEach(function (item, key) {
                    var id = $('.cell_id', item).val();
                    if (id === current) {
                        var prev = items[key-1];
                        var next = items[key+1];
                        sort_data.before = (prev === undefined) ? null : $('.cell_id', prev).val();
                        sort_data.after = (next === undefined) ? null : $('.cell_id', next).val();
                    }
                });
                post_data.sort_data = sort_data;
                $.post("{Yii::$app->urlManager->createUrl('featured/sort-order')}", post_data, function(data, status){
                    if (status == "success") {
                        resetStatement();
                    } else {
                        alert("Request error.");
                    }
                },"html");
            },
            handle: ".handle"
        }).disableSelection();

        saveSortBtn.on('click', function () {
            var post_data = { };
            let sort = "";
            dt.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                var data = this.nodes();
                data.each(function (item) {
                    if ($('.current_sort', item).val() !== undefined) {
                        sort = $('.current_sort', item).val();
                    }
                });
            });

            post_data.order_by = sort;
            $.post("{Yii::$app->urlManager->createUrl('featured/save-current-sort')}", post_data, function(data, status){
                if (status == "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
            switchBtnState(false);
        });

        $('th.sorting').on('click', function () {
            switchBtnState(true);
        });

      $( ".datepicker" ).datepicker({
          changeMonth: true,
          changeYear: true,
          showOtherMonths:true,
          autoSize: false,
          dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
      });
/*
      $("form select[data-role=multiselect]").multipleSelect({
              multiple: true,
              filter: true
      });*/

      $('#filterForm').on('submit', applyFilter);


      $('th.checkbox-column .uniform').click(function() {
          if($(this).is(':checked')){
            $('input:checkbox.uniform-bulkProcess').each(function(j, cb) {
              $(this).prop('checked', true).uniform('refresh');
            });
            $('.switchable').removeClass('disable-btn');
          }else{
            $('input:checkbox:checked.uniform-bulkProcess').each(function(j, cb) {
              $(this).prop('checked', false).uniform('refresh');
            });
              $('.switchable').addClass('disable-btn');
          }
      });

    });

    function resetFilter() {
        $("#row_id").val(0);
        $('select').val('');
        //$("form select[data-role=multiselect]").multipleSelect('refresh');
        $('input').val('');
        resetStatement();
        return false;
    }

    function applyFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;
    }


    function getTableSelectedIds() {
        var selected_messages_ids = [];
        var selected_messages_count = 0;
        $('input:checkbox:checked.uniform').each(function(j, cb) {
            var aaa = $(cb).closest('td').find('.cell_identify').val();
            if (typeof(aaa) != 'undefined') {
                selected_messages_ids[selected_messages_count] = aaa;
                selected_messages_count++;
            }
        });
        return selected_messages_ids;
    }

    function getTableSelectedCount() {
      return $('input:checkbox:checked.uniform-bulkProcess').length;
/*        var selected_messages_count = 0;
        $('input:checkbox:checked.uniform').each(function(j, cb) {
            if ($(this).val() > 0 ) {
                selected_messages_count++;
            }
        });
        return selected_messages_count;*/
    }

    function deleteSelectedFeatured() {
        if (getTableSelectedCount() > 0) {
            var selected_ids = getTableSelectedIds();

            bootbox.dialog({
                    message: "{$smarty.const.TEXT_DELETE_SELECTED}?",
                    title: "{$smarty.const.TEXT_DELETE_SELECTED}",
                    buttons: {
                            success: {
                                    label: "{$smarty.const.TEXT_BTN_YES}",
                                    className: "btn-delete",
                                    callback: function() {
                                        $.post("{Yii::$app->urlManager->createUrl('featured/delete-selected')}", $('input:checkbox:checked.uniform-bulkProcess').serialize(), function(data, status){
                                            if (status == "success") {
                                                resetStatement();
                                            } else {
                                                alert("Request error.");
                                            }
                                        },"html");
                                    }
                            },
                            main: {
                                    label: "{$smarty.const.TEXT_BTN_NO}",
                                    className: "btn-cancel",
                                    callback: function() {
                                            //console.log("Primary button");
                                    }
                            }
                    }
            });
        }
        return false;
    }

    function preEditItem( item_id ) {
        $.post("featured/itempreedit", {
            'item_id': item_id,
            'bp': $('#filterForm').serialize()
        }, function (data, status) {
            if (status == "success") {
                $('#featured_management_data .scroll_col').html(data);
                $("#featured_management").show();
                switchOnCollapse('featured_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        ///$("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function editItem(item_id) {

        $.post("featured/itemedit", {
            'item_id': item_id,
            'featured_type_id': $('.featured-type-id').val(),
        }, function (data, status) {
            if (status == "success") {
                $('#featured_management_data .scroll_col').html(data);
                $("#featured_management").show();
                $(".check_on_off").bootstrapSwitch(
                  {
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                  }
                );
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("featured/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#featured_management_data .scroll_col').html(data);
                $("#featured_management").show();

                $('.table').DataTable().search('').draw(false);

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("featured/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#featured_management_data .scroll_col').html(data);
                $("#featured_management").show();
                switchOnCollapse('featured_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("featured/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#featured_management_data .scroll_col').html("");
                switchOffCollapse('featured_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    
    function switchOffCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-down')) {
            $("#" + id).click();
        }
    }

    function switchOnCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-up')) {
            $("#" + id).click();
        }
    }

    function resetStatement() {
      setFilterState();

//      $('#coupons_management_data').html('');

        switchOnCollapse('featured_list_box_collapse');
        switchOffCollapse('featured_management_collapse');

        $('featured_management_data .scroll_col').html('');
        $('#featured_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }

    function switchStatement(id, status) {
      $.post("featured/switch-status", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      },"html");
    }    
    
    function onClickEvent(obj, table) {
        $('#row_id').val(table.find(obj).index());
        var event_id = $(obj).find('input.cell_identify').val();
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
        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

</script>

<!--===  featured management ===-->
<div class="row right_column" id="featured_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="featured_management_data">
                <div class="scroll_col"></div>
            </div>
        </div> 
</div>
</div>
<!--=== featured management ===-->