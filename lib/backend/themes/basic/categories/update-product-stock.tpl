{use class="common\helpers\Html"}

<div class="popup-heading">{$smarty.const.TEXT_UPDATE_STOCK}</div>
<div class="popup-content">


    <div class="in-out-row">
        <label for="">
            <input type="radio" name="products_quantity_update_prefix" value="+" checked onchange="return checkWarehouseLocation()"/>
            <span><i class="icon-plus"></i> {$smarty.const.TEXT_INBOUND}</span>
        </label>
        <label for="">
            <input type="radio" name="products_quantity_update_prefix" value="-" onchange="return checkWarehouseLocation()"/>
            <span><i class="icon-minus"></i> {$smarty.const.TEXT_OUTBOUND}</span>
        </label>
    </div>
    <div class="amount input-row">
        <label for="">{$smarty.const.TEXT_AMOUNT_FILTER}</label>
        {tep_draw_input_field('products_quantity_update', '', 'class="form-control form-control-small-qty"')}
    </div>
    <div class="amount input-row">
        <label>{\common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories')}</label>
        {Html::textInput('expiry_date', '', ['autocomplete'=>"off", 'class'=> "datepicker form-control form-control-small-qty"])}
    </div>
    <div class="amount input-row">
        <label>{$smarty.const.TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME}</label>
        {Html::textInput('batch_name', '', ['autocomplete'=>"off", 'class'=> "form-control form-control-small-qty"])}
    </div>

    {assign var=list value=\common\helpers\Suppliers::getSuppliersList($products_id)}
    {if is_array($list) and count($list) > 1}
        <div class="input-row">
            <label>{$smarty.const.TEXT_SUPPLIER}</label>
            {Html::dropDownList('w_suppliers_id', $suppliers_id, $list, ['class' => 'form-control form-control-small', 'onchange' => 'return checkWarehouseLocation()'])}
        </div>
    {else}
        {Html::hiddenInput('w_suppliers_id', $suppliers_id)}
    {/if}

    {if \common\helpers\Warehouses::get_warehouses_count() > 1}
        <div class="input-row">
            <label>{$smarty.const.TEXT_WAREHOUSE}:</label>
            {tep_draw_pull_down_menu('warehouse_id', \common\helpers\Warehouses::get_warehouses(), $warehouse_id, 'class="form-control form-control-small" onchange="return checkWarehouseLocation()"')}
        </div>
    {else}
        {tep_draw_hidden_field('warehouse_id', $warehouse_id)}
    {/if}
        <div id="location" class="t-col-1"></div>

    {Html::hiddenInput('update_stock_id', $products_id)}

    <div class="input-row comments">
        <label>{$smarty.const.TEXT_COMMENTS}:</label>
        {tep_draw_input_field('stock_comments', '', 'class="form-control form-control-small"')}
    </div>
    <div class="input-row">
        {\yii\helpers\Html::checkbox('is_autoallocate', 1, ['label' => TEXT_AUTOALLOCATE])}
    </div>
    <div class="">
        <div class="">
            <span class="btn btn-primary" onclick="{$action}('{$products_id}')">{$smarty.const.IMAGE_APPLY}</span>
        </div>
    </div>
</div>
<script type="text/javascript">
    checkWarehouseLocation();
    $(document).ready(function(){
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
        })
    })
</script>