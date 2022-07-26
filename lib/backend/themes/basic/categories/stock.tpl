{use class="\yii\helpers\Url"}
<div class="popup-heading">{$smarty.const.TEXT_SUPPLIERS_WAREHOUSES_STOCK}</div>
<div class="ord_status_filter_row row" style="width: 500px">
  <div class="col-md-3" style="padding-top: 4px">{$smarty.const.TEXT_GROUP_BY}:</div>
  <div class="col-md-5">
      <label for="group_by"><input type="radio" name="group_by" value="supplier" checked />{$smarty.const.BOX_CATALOG_SUPPIERS}</label>
      <label for="group_by"><input type="radio" name="group_by" value="warehouse" />{$smarty.const.BOX_CATALOG_WAREHOUSES}</label>
  </div>
</div>
<div id="warehouses_suppliers_stock_popup"></div>
<script>
function switchType(obj) {
    var type = $(obj).val();
    var url;
    if (type == 'supplier') {
        url = "{Yii::$app->urlManager->createUrl('categories/suppliers-stock')}";
    } else {
        url = "{Yii::$app->urlManager->createUrl('categories/warehouses-stock')}";
    }
    $.get(url, { 'prid' : '{$prid}' }, function (data, status) {
      if (status == "success") {
        $('#warehouses_suppliers_stock_popup').html(data);
      }
    });
}
$('input[name="group_by"]').on('change', function() {
    switchType(this);
    return false;
});
(function($){
    switchType ( $('input[name="group_by"]:checked') );
})(jQuery)
</script>