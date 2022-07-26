{use class="common\helpers\Html"}
{assign var="idPart" value="{$sInfo->suppliers_id}"}
<div class="widget box box-no-shadow supplier-product-widget js-supplier-product" data-supplier-id="{$sInfo->suppliers_id}" style="margin-bottom: 10px;" data-id-part="{$idPart}" id="suppliers{(int)$sInfo->uprid}-{$idPart}">
    <div class="widget-header after">
        <h4>
            <input type="checkbox"
                   name="{'suppliers_data['|cat:$sInfo->uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][status]'}" {if $sInfo->status} checked{/if} value="1" class="supplier-product-status"/>
            {*Html::checkbox('suppliers_data['|cat:$sInfo->uprid|cat:']['|cat:$sInfo->suppliers_id|cat:'][status]', $sInfo->status, ['value'=>1, 'class'=>'supplier-product-status'])*}
            {$sInfo->supplier->suppliers_name}</h4>
        <div class="pull-right">
            <a href="javascript:void(0)" onclick="deleteSupplier({$sInfo->suppliers_id})"><i class="icon-trash color-alert icon-large"></i></a>
        </div>
    </div>
    <div class="widget-content after">
        {$service->set('sProduct', $sInfo)|void}{if !isset($options)}{$options = []}{/if}
        {Html::hiddenInput('suppliers_id['|cat:$sInfo->suppliers_id|cat:']', $sInfo->suppliers_id, $options)}
        {include file="supplier-product-edit-form.tpl" uprid=$sInfo->uprid}
    </div>
</div>
