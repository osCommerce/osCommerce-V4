{use class="\yii\helpers\Url"}
{use class="\yii\helpers\Html"}
<div id="warehouses_relocate_popup">
<div class="popup-heading">{$smarty.const.TEXT_WAREHOUSES_RELOCATE}</div>



    {Html::beginForm(Yii::$app->urlManager->createUrl('categories/warehouses-relocate'), 'post', ['id' => 'warehouses_relocate_form'])}
    {tep_draw_hidden_field('prid', $prid)}
<div class="popup-content">
   
    <div class="warehousesStockPopup">
        <table class="table table-striped table-bordered table-hover table-responsive table-ordering warehouses-stock-datatable double-grid">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th data-orderable="false">{$smarty.const.TEXT_WAREHOUSE}</th>
              <th data-orderable="false">{$smarty.const.TEXT_STOCK_QUANTITY_INFO}</th>
              <th data-orderable="false">{$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}</th>
              <th data-orderable="false">{$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}</th>
              <th data-orderable="false">{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}</th>
              <th data-orderable="false">{$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}</th>
            </tr>
          </thead>
          <tbody>
    {foreach $warehouses as $Item}
            <tr>
              <td>{$Item['sort_order']}</td>
              <td>{$Item['name']}</td>
              <td>{$Item['products_quantity']}</td>
              <td>{$Item['allocated_quantity']}</td>
              <td>{$Item['temporary_quantity']}</td>
              <td>{$Item['warehouse_quantity']}</td>
              <td>{$Item['ordered_quantity']}</td>
            </tr>
    {/foreach}
          </tbody>
        </table>
    </div>


<div class="">
    <div class="input-row">
    {if count(\common\helpers\Suppliers::getSuppliersList($prid)) > 1}
        <label>{$smarty.const.TEXT_SUPPLIER}</label>
      {Html::dropDownList('suppliers_id', $suppliers_id, ['0' => $smarty.const.PULL_DOWN_DEFAULT] + \common\helpers\Suppliers::getSuppliersList($prid), ['class' => 'form-control form-control-small', 'id' => 'suppliers_id'])}
    {else}
        {if $supp_id == 0}
            {assign var=suppliers_id value=\common\helpers\Suppliers::getDefaultSupplierId()}
        {else}
            {$suppliers_id=$supp_id}
        {/if}
        {tep_draw_hidden_field('suppliers_id', $suppliers_id)}
    {/if}
    </div>


    <div class="row">
    {if $suppliers_id > 0}
        <div class="col-md-6">
            <div class="input-row">
                <label>{$smarty.const.TEXT_FROM}:</label>
                {tep_draw_pull_down_menu('from_warehouse', \common\helpers\Warehouses::get_warehouses(), '', 'class="form-control form-control-small" onchange="return checkWarehouseLocationFrom()"')}
            </div>
            <div id="location-from" class="t-col-1"></div>
        </div>

        <div class="col-md-6">
            <div class="input-row">
                <label>{$smarty.const.TEXT_TO}:</label>
                {tep_draw_pull_down_menu('to_warehouse', \common\helpers\Warehouses::get_warehouses(), '', 'class="form-control form-control-small" onchange="return checkWarehouseLocationTo()"')}
            </div>
            <div id="location-to" class="t-col-1"></div>
            <div class="input-row" style="float:left">
                <label>{\common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories')}</label>
                {Html::textInput('expiry_date', '', ['autocomplete'=>"off", 'class'=> "datepicker form-control form-control-small-qty"])}
            </div>
            <div class="input-row" style="float:left">
                <label>{$smarty.const.TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME}</label>
                {Html::textInput('batch_name', '', ['autocomplete'=>"off", 'class'=> "form-control form-control-small-qty"])}
            </div>
        </div>
    {/if}
    </div>
</div>


</div>
    <div class="mail-sending noti-btn" style="clear: both">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><input class="btn btn-primary" type="submit" value="{$smarty.const.IMAGE_UPDATE}"></div>
    </div>
    {Html::endForm()}
<script>
function checkWarehouseLocationFrom() {
    var warehouse_id = $('[name="from_warehouse"]').val();
    var suppliers_id = $('[name="suppliers_id"]').val();
    var products_id = $('[name="prid"]').val();
    var prefix = '-';
    $.post("categories/warehouse-location", { 'warehouse_id' : warehouse_id, 'suppliers_id' : suppliers_id, 'prefix' : prefix, 'products_id' : products_id }, function(data, status) {
        if (status == "success") {
            $('#location-from').html(data);
        }
    },"html");
    return false;
}
function checkWarehouseLocationTo() {
    var warehouse_id = $('[name="to_warehouse"]').val();
    var suppliers_id = $('[name="suppliers_id"]').val();
    var products_id = $('[name="prid"]').val();
    var prefix = '+';
    $.post("categories/warehouse-location", { 'warehouse_id' : warehouse_id, 'suppliers_id' : suppliers_id, 'prefix' : prefix, 'products_id' : products_id }, function(data, status) {
        if (status == "success") {
            $('#location-to').html(data);
        }
    },"html");
    return false;
}

  var table;
  (function($){
    table = $('.warehouses-stock-datatable').dataTable( {
        'pageLength': 10,
        'order': [[ 0, 'asc' ]],
        'columnDefs': [ { 'visible': false, 'targets': 0 } ],
        'bFilter': false
    } );
    var oSettings = table.fnSettings();
    oSettings._iDisplayStart = 0;
    table.fnDraw();
    
    checkWarehouseLocationFrom();
    checkWarehouseLocationTo();
    
    $( ".datepicker" ).datepicker({
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
        dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
        onSelect: function (e) {
            if ($(this).val().length > 0) {
                $(this).siblings('span').addClass('active_options');
            } else {
                $(this).siblings('span').removeClass('active_options');
            }
        }
    })
  })(jQuery)

  $('#warehouses_relocate_form').on('submit', function() {
    $.post("{Yii::$app->urlManager->createUrl('categories/warehouses-relocate')}", $('#warehouses_relocate_form').serialize(), function (data, status) {
      if (status == "success") {
        $('#warehouses_relocate_popup').replaceWith(data);
      }
    });
    return false;
  });

  $('#suppliers_id').on('change', function() {
    $.get('{Yii::$app->urlManager->createUrl('categories/warehouses-relocate')}', { prid: '{$prid}', suppliers_id: $('#suppliers_id').val() }, function(data, status){
      if (status == 'success') {
        $('#warehouses_relocate_popup').replaceWith(data);
      }
    });
  });
</script>
</div>