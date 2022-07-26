<!-- /Page Header -->
{use class="common\helpers\Html"}
{\backend\assets\OrderAsset::register($this)|void}
{\backend\assets\MultiSelectAsset::register($this)|void}
<!--=== Page Content ===-->
<div class="widget box box-wrapp-blue filter-wrapp widget-fixed">

    <div class="widget-header filter-title">
        <h4>
          {$smarty.const.TEXT_FILTER} <form id="filterFormHead" name="filterFormHead" class="filterFormHead" onsubmit="return applyFilter();"><input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" /><button type="submit" class="btn">{$smarty.const.TEXT_GO}</button></form>
        </h4>
    </div>

    <div class="widget-content">

            <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                {include file="../filters/orders-ga.tpl"}
                <div class="filters_btn">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
                    <input type="hidden" name="fs" value="{$app->controller->view->filters->fs}" />
                </div>
            </form>

    </div>

</div>

<!--===Orders List ===-->
<div class="order-wrap">    
<div class="row order-box-list order-sc-text" style="width:100%">
    <div class="col-md-12">
        <div class="widget-content">
            <div class="btn-wr after btn-wr-top btn-wr-top1 disable-btn batch-actions">
                <div>
                    <span class="batch-actions-label">{$smarty.const.TEXT_BATCH_ACTIONS}:</span>
                    {*<a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a>*}
                    <a href="javascript:void(0)" onclick="changeStatus();" class="btn btn-chng">{$smarty.const.TEXT_SEND_DETAILS}</a>
                </div>
                <div>
                </div>
            </div>   

            <table class="table table-striped table-bordered table-hover table-responsive table-checkable  datatable dataTable"

                    data_ajax="orders-ga/orderlist"
                    checkable_list="{$app->controller->view->sortColumns}"
                    order_list="{$app->controller->view->sortNow}"
                    order_by="{$app->controller->view->sortNowDir}"
                   >

                <thead>
                  <tr>
                    {foreach $app->controller->view->ordersTable as $tableItem}
                    <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column sorting_disabled"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                    {/foreach}
                  </tr>
                </thead>

            </table>
            <div class="btn-wr after disable-btn batch-actions">
                <div>
                    <span class="batch-actions-label">{$smarty.const.TEXT_BATCH_ACTIONS}:</span>
                    {*<a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a>*}
                    <a href="javascript:void(0)" onclick="changeStatus();" class="btn btn-chng">{$smarty.const.TEXT_SEND_DETAILS}</a>
                </div>
                <div>
                </div>
            </div>                
        </div>
    </div>
</div>
<!-- /Orders List -->

        
                                
<script type="text/javascript">
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
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_count++;
        }
    });
    return selected_messages_count;
}
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
{*
function cancelStatement() {
    var orders_id = $('.table tbody tr.selected').find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('orders-ga/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
}
*}
function setFilterState() {
    orig = $('#filterForm, #filterFormHead, #filterModeForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}
function resetStatement(reset) {
    setFilterState();
    $("#order_management").hide();
    switchOnCollapse('orders_list_collapse');
    var table = $('.table').DataTable();
    table.draw(reset);
    $(window).scrollTop(0);
    return false;
}
{*
function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    var orders_id = $(obj).find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('orders-ga/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
}
*}
function onUnclickEvent(obj, table) {

}

$(document).ready(function() {

    $(window).resize(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800);        
    })
    $(window).resize();
    
    
    $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
    $('.w-tdc.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.w-tdc').removeClass('act_row');
            $(this).parents('.w-tdc').addClass('act_row');
            $('.w-tdc input[type="text"]').prop('disabled', true);
            $('.w-tdc select').prop('disabled', true);
            $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
            $('.w-tdc.act_row select').prop('disabled', false);
        }
    });

    $('#fcById').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcLike").prop("checked", false);
          $("#fcLike").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        } else {
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        }
      }
    );
    $('#fcLike').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcById").prop("checked", false);
          $("#fcById").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        } else {
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        }
      }
    );


    $('#fpFrom').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpFromSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpFromSumm").prop("disabled", true);
          if (!$("#fpTo").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );
    $('#fpTo').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpToSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpToSumm").prop("disabled", true);
          if (!$("#fpFrom").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );
//
    $('th.checkbox-column .uniform').on('click', function() {
        if ($(this).is(':checked')) {
          $('td input:checkbox.uniform').each(function(j, cb) {
            $(this).prop('checked', true).uniform('refresh');
          });
          $('.order-box-list .btn-wr').removeClass('disable-btn');
        } else {
          $('td input:checkbox:checked.uniform').each(function(j, cb) {
            $(this).prop('checked', false).uniform('refresh');
          });
          $('.order-box-list .btn-wr').addClass('disable-btn');
        }
        /*
        if($(this).is(':checked')){
            $('tr.checkbox-column .uniform').prop('checked', true).uniform('update');
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }*/
    });

      $('.datatable').on('draw.dt', function () {
        var $main_switch = $('th.checkbox-column .uniform');
        var have_checked = $(this).find('.uniform').not($main_switch).filter(':checked').length > 0;
        if ($main_switch.get(0).checked) {
          $main_switch.get(0).checked = false;
          $.uniform.update();
        }
        if (have_checked) {
          $('.order-box-list .btn-wr').removeClass('disable-btn');
        } else {
          $('.order-box-list .btn-wr').addClass('disable-btn');
        }
      });
    
    $('select.select2-offscreen').change(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800); 
    });

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
    {if $departments}
    var $departments = $('.js_department_checkboxes');
    var check_department_checkboxes = function(){
        var checked_all = true;
        $departments.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $departments.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_department_checkboxes();
    $departments.on('click',function(){
        var self = this;
        if (this.value=='') {
            $departments.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $departments.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $departments.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {/if}
});

function resetFilter() {
    $('select[name="by"]').val('');
    $('input[name="search"]').val('');
    $("#presel").prop("checked", true);
    $("#exact").prop("checked", false);
    $('.js_platform_checkboxes').prop("checked", false);
    $('.js_department_checkboxes').prop("checked", false);
    $('select[name="interval"]').val('');
    $('input[name="from"]').val('');
    $('input[name="to"]').val('');
    $('select[name="status"]').val('');
    $('input[name="delivery_country"]').val('');
    $('input[name="delivery_state"]').val('');
    $("#fcById").prop("checked", true);
    $("#fcLike").prop("checked", false);
    $("#fcId").val('');
    $("#fcCode").val('');
    $("#fcCode").prop('disabled', true);
    $("#fcId").prop('disabled', false);

    $("#fpFrom").prop("checked", false);
    $("#fpTo").prop("checked", false);
    $("#fpClass").val('');
    $("#fpFromSumm").val('');
    $("#fpToSumm").val('');
    $("#fpFromSumm").prop('disabled', true);
    $("#fpToSumm").prop('disabled', true);
    $("#fpClass").prop('disabled', true);
    $('select[name="walkin"]').val('');
    $("input[name='flag'][value='0']").prop("checked", true);
    $("input[name='marker'][value='0']").prop("checked", true);
    $("select[data-role=multiselect]").multipleSelect('uncheckAll');
    $("select[data-role=multiselect-radio]").multipleSelect('uncheckAll');

    $("#row_id").val(0);
    $('label.active_options, span.active_options').removeClass('active_options');
    resetStatement();
    return false;  
}
    
function applyFilter() {
    resetStatement();
    return false;    
}

function exportSelectedOrders() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.target = "_blank";
        form.method = "POST";
        form.action = 'orders-ga/ordersexport';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }
    
    return false;
}

function reversGA(oid) {
  bootbox.confirm('{$smarty.const.TEXT_CONFIRM_REVERSE|escape:"javascript"}', function(result){
    if (result){
      $.post("{$app->urlManager->createUrl('orders-ga/reverse')}", { 'orders_id' : oid }, function(data, status){
        if (status == "success") {
          if (data.error == 0) {
            //alert(data.message);
            resetStatement(true);
          } else {
            if (data.message != '') {
              alert(data.message);
            } else {
              alert("Request error.");
            }
          }
        } else {
            alert("Request error.");
        }
      },"json");
    }
  });

  return false;
}

function changeStatus() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.method = "POST";
        form.action = 'orders-ga/send-ecommerce';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }

    return false;
}

$(document).ready(function(){
	//===== Date Pickers  =====//
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
        $("select[data-role=multiselect]").multipleSelect({
            multiple: true,
            filter: true,
        });
		
		$('[data-role=multiselect-radio]').multipleSelect({
            multiple: false,
            filter: true,
            single: true,
            onClick : function(option){
                applyFilter();
            }
        });
 
});

</script>

</div>

<!-- /Page Content -->
