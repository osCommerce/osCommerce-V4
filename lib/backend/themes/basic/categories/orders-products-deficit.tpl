{use class="\yii\helpers\Url"}
<div class="popup-heading">{$smarty.const.TEXT_ORDER_ALLOCATE_DEFICIT}</div>
<div class="creditHistoryPopup">
    <table class="table table-striped table-bordered table-hover table-responsive table-ordering stock-history-datatable double-grid">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
          <th data-orderable="false">{$smarty.const.TEXT_CUSTOMER_NAME}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_ORDER}</th>
          <th data-orderable="false">{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</th>
        </tr>
      </thead>
      <tbody>
      {foreach $deficitArray as $deficitRecord}
        <tr id="opdid_{$deficitRecord['orders_products_id']}">
          <td>{$deficitRecord['orders_products_id']}</td>
          <td>{$deficitRecord['datetime']}</td>
          <td>{$deficitRecord['customer_name']}</td>
          <td>{$deficitRecord['order_link']}</td>
          <td>{$deficitRecord['deficit']}</td>
        </tr>
{/foreach}
      </tbody>
    </table>
</div>
<div class="mail-sending noti-btn">
  <div></div>
  <div><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_OK}</span></div>
</div>
<script>
    var table;
    (function($){
        table = $('.stock-history-datatable').dataTable({
            'pageLength': 5,
            'order': [[0, 'desc']],
            'columnDefs': [{ 'visible': false, 'targets': 0 }],
        });
        var oSettings = table.fnSettings();
        oSettings._iDisplayStart = 0;
        table.fnDraw();
    })(jQuery);
</script>