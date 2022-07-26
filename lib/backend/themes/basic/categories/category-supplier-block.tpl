{use class="\common\helpers\Html"}
{assign var=supplierRO value=$sInfo['supplierRO']}
{assign var=rulesRO value=$sInfo['rulesRO']}
{assign var=discountRO value=$sInfo['discountRO']}
<div class="{if not $singleSupplier}widget box box-no-shadow {/if}js-supplier-rule-set" style="margin-bottom: 10px;" id="suppliers-{$sInfo['info']['suppliers_id']}" data-supplier-id="{$sInfo['info']['suppliers_id']}" data-rule-default="{$sInfo['default_rule']|json_encode|escape:'html'}">
    {if not $singleSupplier}
    <div class="widget-header after">
        <h4>{$sInfo['info']['suppliers_name']}</h4>
        {if not $supplierRO}<div class="js-supplier-rule-set-remove pull-right"><a href="javascript:void(0)"><i class="icon-trash color-alert"></i></a></div>{/if}
    </div>
    {/if}
    <div {if not $singleSupplier}class="widget-content after"{/if}>
        <div class="row">
            <div class="col-md-3">
                {if $discountRO && (!$sInfo['discount_table'] || count($sInfo['discount_table'])==0)}
                <div class="widget box widget-header">
                    <div><h4>{$smarty.const.TEXT_SUPPLIER_NO_QUANTITY_DISCOUNT}</h4></div>
                </div>
                {else}
                <div class="widget box box-no-shadow">
                    <div class="widget-header"><h4>{$smarty.const.TITLE_QUANTITY_DISCOUNT}</h4>{if not $discountRO}<div class="pull-right">{Html::checkbox('suppliers_data['|cat:$sInfo['info']['suppliers_id']|cat:'][has_discount_table]',!empty($sInfo['discount_table']),['value'=>'1','class'=>'js-supplier-qdt-switcher check_on_off'])}</div>{/if}</div>
                    <div class="widget-content js-qdt">
                        <table class="table table-bordered js-qdt-table" width="100%" data-index="{($sInfo['discount_table']|@count)+1}">
                            <thead>
                            <tr>
                                <th>{$smarty.const.TEXT_FROM}</th>
                                <th>{$smarty.const.TEXT_TO}</th>
                                <th>{$smarty.const.TEXT_SUPPLIER_DISCOUNT_PERCENT}</th>
                                {if not $discountRO}
                                <th>&nbsp;</th>
                                {/if}
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $sInfo['discount_table'] as $_idx=>$discountData}
                                {include file="../categories/supplier-quantity-discount-row.tpl" supplier_idx=$sInfo['info']['suppliers_id'] row_idx=$_idx}
                            {/foreach}
                            </tbody>
                        </table>
                        {if not $discountRO}
                        <div class="btn-bar"><div class="text-right"><button type="button" class="btn btn-add-more js-qdt-add-row">{$smarty.const.TEXT_ADD_MORE}</button></div></div>
                        {/if}
                    </div>
                </div>
                {/if}
            </div>
            {if $mayEditCost}
            <div class="col-md-9">
                <div class="widget box box-no-shadow">
                    <div class="widget-header"><h4>{$smarty.const.TITLE_SUPPLIER_COST}</h4></div>
                    <div class="widget-content">
                        <table class="table table-bordered js-table-rules" width="100%">
                            <thead>
                            <tr>
                                <th>{$smarty.const.TEXT_CURRENCY}</th>
                                <th class="js-cond js-cond-fromTo">{$smarty.const.TEXT_COST_FROM}</th>
                                <th class="js-cond js-cond-fromTo">{$smarty.const.TEXT_COST_TO}</th>
                                <th class="js-cond js-cond-notBelow">{$smarty.const.TEXT_COST_NOT_BELOW}</th>
                                <th>{$smarty.const.TEXT_PRICE_FORMULA}</th>
                                <th>{$smarty.const.TEXT_SUPPLIER_DISCOUNT}</th>
                                <th>{$smarty.const.TEXT_SURCHARGE}</th>
                                <th>{$smarty.const.TEXT_MARGIN}</th>
                                {if not $rulesRO}
                                <th>&nbsp;</th>
                                {/if}
                            </tr>
                            </thead>
                            <tbody class="js-supplier-rule-table" data-index="{count($sInfo['price_rule'])+1}">
                            {foreach $sInfo['price_rule'] as $_idx=>$rule}
                                {include file="../categories/supplier-price-rule.tpl" supplier_idx=$sInfo['info']['suppliers_id'] supplier_rule_idx=$_idx default_rule=$sInfo['default_rule'] currenciesVariants = $sInfo['currenciesVariants']}
                            {/foreach}
                            </tbody>
                        </table>
                        {Html::hiddenInput('suppliers_data['|cat:$sInfo['info']['suppliers_id']|cat:'][rule_condition]', $sInfo['rule_condition'], ['class'=>'js-row-condition'])}
                        {if not $discountRO}
                        <div class="btn-bar">
                            <div class="text-right">
                                <button type="button" class="btn btn-add-more js-change-supplier-rule-condition">{$smarty.const.TEXT_ADD_CONDITION}</button>
                                <button type="button" class="btn btn-add-more js-add-supplier-rule">{$smarty.const.TEXT_ADD_MORE}</button>
                            </div>
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
            {/if}
        </div>
        {if $supplierRO}
            <div class="row">
                <div class="col-md-12">
                    <button class="btn pull-right js-ro-edit" type="button">{$smarty.const.IMAGE_EDIT}</button>
                </div>
            </div>
        {/if}
    </div>
<script type="text/template" id="supplier-price-rule-tpl-{$sInfo['info']['suppliers_id']}">
    {include file="../categories/supplier-price-rule.tpl" supplier_idx='__supplier_id__' supplier_rule_idx='__supplier_rule_id__' currenciesVariants=$sInfo['currenciesVariants']}
</script>
</div>
