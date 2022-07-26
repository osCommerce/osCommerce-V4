{use class="yii\helpers\Html"}
<div class="popupCategory">

    <table cellspacing="0" cellpadding="0" width="100%" border="0">
{if {$app->controller->view->suppliers|@count} > 0}
    {foreach $app->controller->view->suppliers as $suppliers_id => $supplier}
        <tr>
            <td class="label_name">{$smarty.const.TEXT_SUPPLIER} {$supplier['suppliers_name']}</td>
            <td class="label_name">{$smarty.const.TEXT_STOCK_QTY} {$supplier['suppliers_quantity']}</td>
            <td class="label_name">{$smarty.const.TEXT_CALCULATED_PRICE}</td>
            <td class="label_name">{$smarty.const.TEXT_NET} {$supplier['suppliers_calculated_price_net']}</td>
            <td class="label_name">{$smarty.const.TEXT_GROSS} {$supplier['suppliers_calculated_price_gross']}</td>
            <td class="label_name">{$smarty.const.TEXT_PROFIT} {$supplier['suppliers_calculated_profit']}</td>
            <td class="label_name">{$smarty.const.BOX_SUPPLIER_PRIORITY}: {$supplier['priorityWeight']}</td>
            <td class="label_value"><a href="javascript:void(0)" class="btn {if $supplier['is_preferred']} btn-primary{/if}" onclick="selectSupplierPrice({$suppliers_id}, '{$supplier['target_id']}'); return cancelStatement();">{$smarty.const.IMAGE_SELECT}</a></td>
        </tr>
    {/foreach}
{/if}
    </table>

    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return cancelStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
    </div>
</div>

<script type="text/javascript">
function cancelStatement() {
    $('.popup-box:last').trigger('popup.close');
    $('.popup-box-wrap:last').remove();
    return false;
}
</script>
