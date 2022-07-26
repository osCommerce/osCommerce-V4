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
                <div class="col-sm-3">
                    <select class="form-control form-control-small" name="by">
                        {foreach $app->controller->view->filters->by as $Item}
                            <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-sm-6">
                    {Html::textInput('search', $app->controller->view->filters->search)}
                </div>
            </div>

            <div class="row m-b-2">
                <div class="col-sm-3 align-right">
                    <label for="inactive">{$smarty.const.TEXT_INACTIVE}:</label>
                </div>
                <div class="col-sm-3">
                    {Html::checkbox('inactive', $app->controller->view->filters->inactive)}
                </div>
            </div>

            <div class="row m-b-2 align-items-center">
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
                <div class="col-sm-2">
                    {Html::dropDownList('date', $app->controller->view->filters->date, $app->controller->view->filters->dateOptions, ['class' => "form-control form-control-small"])}
                </div>
            </div>

            <div class="row m-b-2 align-items-center">
                <div class="col-sm-3 align-right">
                    <label>{$smarty.const.COUPON_AMOUNT}:</label>
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
                    <a href="javascript:void(0)" target="_blank" style="margin-left: 8px" class="btn js-export-search">Export search result</a>
                </div>
            </div>
              <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
          {Html::endForm()}
        </div>
    </div>
</div>
<!--===reviews list===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="reviews_list_data">
                <table class="table-coupon_admin table-statuses table table-striped table-bordered table-hover table-responsive table-checkable datatable"
                       checkable_list="{$app->controller->view->sortColumns}" data_ajax="coupon_admin/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->couponTable as $tableItem}
                            <th class="{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} checkbox-column sorting_disabled"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} hidden-xs{/if}">{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--===/reviews list===-->

<script type="text/javascript">

    $(document).ready(function(){
        $('.js-export-search').on('click', function(){
            $('#filterForm').trigger('submit');
            $(this).attr('href', '{Yii::$app->urlManager->createUrl(['coupon_admin/list','export'=>1])}&filter='+encodeURIComponent($('#filterForm').serialize()));
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
      
      $('.btn-import').each(function() {
            $(this).dropzone({
              url: '{Yii::$app->urlManager->createUrl('coupon_admin/import')}',
              success: function(){
                location.reload();
              }
            })
        });

    });

    function resetFilter() {
        $("#row_id").val(0);
        $('select').val('');
        $("form select[data-role=multiselect]").multipleSelect('refresh');
        $('input').val('');
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

    function deleteSelectedGWA() {
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
                                        $.post("{Yii::$app->urlManager->createUrl('coupon_admin/delete-selected')}", $('input:checkbox:checked.uniform-bulkProcess').serialize(), function(data, status){
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
        $.post("coupon_admin/itempreedit", {
            'item_id': item_id,
            'bp': $('#filterForm').serialize()
        }, function (data, status) {
            if (status == "success") {
                $('#coupons_management_data').html(data);
                $("#coupons_management").show();
                switchOnCollapse('coupons_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        //$("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function saveItem() {
        $.post("coupon_admin/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#coupons_management_data').html(data);
                $("#coupons_management").show();

                $('.table').DataTable().search('').draw(false);

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("coupon_admin/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#coupons_management_data').html(data);
                $("#coupons_management").show();
                switchOnCollapse('coupons_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("coupon_admin/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#coupons_management_data').html("");
                switchOffCollapse('coupons_management_collapse');
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

    function onClickEvent(obj, table) {
        var dtable = $(table).DataTable();
        var id = dtable.row('.selected').index();
        $("#row_id").val(id);
        setFilterState();

        var event_id = $(obj).find('input.cell_identify').val();

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

<!--===  coupons management ===-->
<div class="row right_column" id="coupons_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="coupons_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== coupons management ===-->
</div>
