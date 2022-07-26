{use class="common\helpers\Html"}
{assign var="idPart" value="{str_replace(['{', '}'], ['-', '-'], $uprid)}-{$sInfo->suppliers_id}"}
<div class="widget box box-no-shadow supplier-product-widget js-supplier-product" style="margin-bottom: 10px;" data-id-part="{$idPart}" data-supplier-id="{$sInfo->suppliers_id}" id="suppliers{$idPart}">
    <div class="widget-header after">
        <h4>{Html::checkbox('suppliers_data['|cat:$uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][status]', $sInfo->status, ['class'=>'supplier-product-status _on_off'])} {$sInfo->supplier->suppliers_name}</h4>
        <div class="pull-right">
            <a href="javascript:void(0)" onclick="deleteSupplierInv({$sInfo->suppliers_id}, '{str_replace(['{', '}'], ['-', '-'], $uprid)}')"><i class="icon-trash color-alert icon-large"></i></a>
        </div>
    </div>
    <div class="widget-content after">
        {Html::hiddenInput('suppliers_id_'|cat:$uprid|cat:'['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_id, $options)}
        {include file="@backend/themes/basic/categories/supplier-product-edit-form.tpl"}
    </div>
</div>
<script>
$(document).ready(function() {

  initBTiStatus($('#suppliers{$idPart} .supplier-product-status'));

});
</script>
