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
      <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
        <table width='100%' cellspacing="3" cellpadding="1" border="0">
          <tbody>
          <tr>
            <td align="right">
              <label>{$smarty.const.TEXT_SEARCH_BY}</label>
            </td>
            <td>
              <select class="form-control form-control-small" name="by">
                {foreach $app->controller->view->filters->by as $Item}
                  <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                {/foreach}
              </select>
            </td>
            <td>
              {Html::textInput('search', $app->controller->view->filters->search)}
            </td>
          </tr>

          <tr>
            <td colspan='3' align='center'>
              <div class="f_row"><br>
                <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                <a href="javascript:void(0)" target="_blank" style="margin-left: 8px"  class="btn js-export-search">Export report</a>
              </div>
            </td>
          </tr>

          </tbody>
        </table>
        <input type="hidden" name="cid" value="{$app->controller->view->filters->coupon_id}">
        <input type="hidden" name="row" id="row_id" value="{$app->controller->view->row_id}" />

      </form>
    </div>

  </div>
</div>
<!--===reviews list===-->
<div class="order-wrap">
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content" id="reviews_list_data">
        <table class="table table-striped table-bordered table-ordering table-hover table-responsive table-checkable datatable"
               order_list="3" order_by="desc"
               checkable_list="0,1,2,3" data_ajax="coupon_admin/report-usage-list">
          <thead>
          <tr>
            {foreach $app->controller->view->catalogTable as $tableItem}
              <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
            {/foreach}
          </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!--===/reviews list===-->
  <script type="text/javascript">
      function applyFilter() {
          $("#row_id").val(0);
          resetStatement();
          return false;
      }
      function resetFilter() {
        $("#row_id").val(0);
        $('select').val('');
        //$("form select[data-role=multiselect]").multipleSelect('refresh');
        $('input').val('');
        resetStatement();
        return false;
      }
      function setFilterState() {
          orig = $('#filterForm').serialize();
          var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
          window.history.replaceState({ }, '', url);
      }

      function preEditItem( item_id ) {
          $.post("coupon_admin/report-usage-info", {
              'item_id': item_id
          }, function (data, status) {
              if (status == "success") {
                  $('#reviews_management_data .scroll_col').html(data);
                  $("#reviews_management").show();
                  // switchOnCollapse('reviews_management_collapse');
              } else {
                  alert("Request error.");
              }
          }, "html");
          return false;
      }
      function resetStatement() {
          $("#reviews_management").hide();

          //switchOnCollapse('reviews_list_box_collapse');
          //switchOffCollapse('reviews_management_collapse');

          $('reviews_management_data').html('');
          $('#reviews_management').hide();

          var table = $('.table').DataTable();
          table.draw(false);

          $(window).scrollTop(0);

          return false;
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

      $(document).ready(function() {
        $('.js-export-search').on('click', function () {
          $(this).attr('href', '{Yii::$app->urlManager->createUrl(['coupon_admin/report-usage-list','export'=>1])}&filter=' + encodeURIComponent($('#filterForm').serialize()));
          $('#filterForm').trigger('submit');
        });
      });
  </script>
  <!--===  reviews management ===-->
  <div class="row right_column" id="reviews_management">
    <div class="widget box">
      <div class="widget-content fields_style" id="reviews_management_data">
        <div class="scroll_col"></div>
      </div>
    </div>
  </div>
</div>
<!--=== reviews management ===-->