{use class="\yii\helpers\Url"}
<div class="popup-heading">{$smarty.const.TEXT_TEMPORARY_STOCK}</div>
<div class="creditHistoryPopup">
    <table class="table table-striped table-bordered table-hover table-responsive table-ordering stock-history-datatable double-grid">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
          <th data-orderable="false">{$smarty.const.TEXT_WAREHOUSE}</th>
          <th data-orderable="false">{$smarty.const.TEXT_CUSTOMER_NAME}</th>
          <th data-orderable="false">{$smarty.const.TEXT_SESSION_ID}</th>
          <th data-orderable="false">{$smarty.const.TEXT_PRODUCTS_QUANTITY_INFO}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_ACTION}</th>
        </tr>
      </thead>
      <tbody>
{foreach $temporaryArray as $temporaryRecord}
        <tr id="tstrid_{$temporaryRecord['temporary_stock_id']}">
          <td>{$temporaryRecord['temporary_stock_id']}</td>
          <td>{$temporaryRecord['temporary_stock_datetime']}</td>
          <td>{$temporaryRecord['warehouse_name']}</td>
          <td>{$temporaryRecord['customer_name']}</td>
          <td>{$temporaryRecord['session_id']}</td>
          <td>{$temporaryRecord['temporary_stock_quantity']}</td>
          <td><div class="del-pt" onclick="deleteOrdersProductsTemporaryStockConfirm('{$temporaryRecord['temporary_stock_id']}');"></div></td>
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

    function deleteOrdersProductsTemporaryStockConfirm(temporary_stock_id) {
        if (confirm('{$smarty.const.MESSAGE_STOCK_TEMPORARY_DELETE_CONFIRM|replace:'\'':'\\\''}')) {
            $.post('{Yii::$app->urlManager->createUrl('categories/temporary-stock')}', { temporary_stock_id: temporary_stock_id, action: 'delete' }, function(response, status) {
                if (status == 'success') {
                    isReload = true;
                    $('div.creditHistoryPopup table tr#tstrid_' + response.id).remove();
                }
            }, 'json');
        }
        return false;
    }
</script>