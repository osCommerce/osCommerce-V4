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
          {Html::beginForm('coupon_admin', 'get', ['id'=>'filterForm', 'name'=>'coupons-form'])}
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
                    <label for="specialsTypeId">{$smarty.const.TEXT_SPECIALS_TAGS}:</label>
                </div>
                <div class="col-sm-4">
                    {Html::dropDownList('specials_type_id', $selected_type_id, $app->controller->view->types, ['class'=>"form-control"])}
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
                    <label for="specialsTypeId">{$smarty.const.BOX_CUSTOMERS_GROUPS}:</label>
                </div>
                <div class="col-sm-4">
                    {Html::dropDownList('group_id', $group_id, $app->controller->view->groups|default:array(), ['class'=>"form-control"])}
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
                    {Html::textInput('dfrom', $app->controller->view->filters->dfrom, ['class' => "datepicker form-control", 'autocomplete' => 'off'])}
                </div>
                <div class="col-sm-1 align-right">
                    <label>{$smarty.const.TEXT_TO}</label>
                </div>
                <div class="col-sm-2">
                    {Html::textInput('dto', $app->controller->view->filters->dto, ['class' => "datepicker form-control", 'autocomplete' => 'off'])}
                </div>
                <div class="col-sm-3">
                    {Html::dropDownList('date', $app->controller->view->filters->date, $app->controller->view->filters->dateOptions, ['class' => "form-control"])}
                </div>
            </div>

            <div class="row m-b-2">
                <div class="col-sm-3 align-right">
                    <label>{$smarty.const.TEXT_PRODUCTS_PRICE_INFO}:</label>
                </div>
                <div class="col-sm-1 align-right">
                    <label>{$smarty.const.TEXT_FROM}</label>
                </div>
                <div class="col-sm-2">
                    {Html::textInput('pfrom', $app->controller->view->filters->pfrom, ['class' => "form-control form-control-small", 'autocomplete' => 'off'])}
                </div>
                <div class="col-sm-1 align-right">
                    <label>{$smarty.const.TEXT_TO}</label>
                </div>
                <div class="col-sm-2">
                    {Html::textInput('pto', $app->controller->view->filters->pto, ['class' => "form-control form-control-small", 'autocomplete' => 'off'])}
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

<div class="order-wrap">
<!--===specials list===-->
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="specials_list_data">
                <div class="btn-wr after btn-wr-top disable-btn">
                    <div>
                        <a href="javascript:void(0)" onclick="deleteSelectedSales();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                    </div>
                    <div>
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid table-statuses table-specials"
                       checkable_list="{$app->controller->view->sortColumns}"
                       order_list="{$app->controller->view->sortNow}"
                       order_by="{$app->controller->view->sortNowDir}"
                       data_ajax="specials/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->specialsTable as $tableItem}
                            <th class="{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} checkbox-column sorting_disabled{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} hidden-xs{/if}">{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>

    </div>
</div>
<!--===/specials list===-->

<script type="text/javascript">
    $(document).ready(function(){

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

    });

    function resetFilter() {
        $("#row_id").val(0);
        $('select').val('');
        $('input[type="text"]').val('');
        //$("form select[data-role=multiselect]").multipleSelect('refresh');
        try {
          $('form  input:checkbox:checked.uniform').each(function(j, cb) {
            $(this).prop('checked', false).uniform('refresh');
          });
        } catch (e) { }
        resetStatement();
        return false;
    }

    function applyFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;
    }

    function resetStatement() {
      setFilterState();

      switchOnCollapse('coupons_list_box_collapse');
      switchOffCollapse('coupons_management_collapse');

      $('#coupons_management_data').html('');
      $('#coupons_management').hide();

      var table = $('.table').DataTable();
      table.draw(false);

       $(window).scrollTop(0);
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

    function deleteSelectedSales() {
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
                                        $.post("{Yii::$app->urlManager->createUrl('specials/delete-selected')}", $('input:checkbox:checked.uniform-bulkProcess').serialize(), function(data, status){
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
        $.post("specials/itempreedit", {
            'item_id': item_id,
            'bp': $('#filterForm').serialize()
        }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        //$("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function editItem(item_id) {

        $.post("specials/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("specials/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#specials_management_data .scroll_col').html(data);
                $("#specials_management").show();
                switchOnCollapse('specials_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("specials/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#specials_management_data .scroll_col').html("");
                switchOffCollapse('specials_management_collapse');
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
        $("#specials_management").hide();

        switchOnCollapse('specials_list_box_collapse');
        switchOffCollapse('specials_management_collapse');

        $('specials_management_data').html('');
        $('#specials_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        //$(window).scrollTop(0);

        return false;
    }

    function switchStatement(id, status) {
      $.post("specials/switch-status", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
          resetStatement();
        } else {
          alert("Request error.");
        }
      },"html");
    }
    
    function onClickEvent(obj, table) {
      var dtable = $(table).DataTable();
      var id = dtable.row('.selected').index();
      $("#row_id").val(id);
      setFilterState();
      var event_id = $(obj).find('input.cell_identify').val();

      $(".check_on_off").bootstrapSwitch({
        onSwitchChange: function (element, arguments) {
          switchStatement(element.target.value, arguments);
          return true;
        },
      });
      preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

    $('th.checkbox-column .uniform').click(function() {
        if($(this).is(':checked')){
          $('input:checkbox.uniform-bulkProcess').each(function(j, cb) {
            $(this).prop('checked', true).uniform('refresh');
          });
          $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
          $('input:checkbox:checked.uniform-bulkProcess').each(function(j, cb) {
            $(this).prop('checked', false).uniform('refresh');
          });
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
</script>

<!--===  specials management ===-->
<div class="row right_column" id="specials_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="specials_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
</div>
<!--=== specials management ===-->