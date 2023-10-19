{use class="\common\helpers\Product"}
{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{use class="\backend\design\editor\Formatter"}
<tr class="dataTableRow {$rowClass}{if $product['parent_product']} child-product{/if}" data-opid="{$product['orders_products_id']}" data-sortKey="{$product['orders_products_id']}">
    <td class="dataTableContent table-image-td order-product-image" valign="top" align="center">
        <div class="table-image-cell">{Html::a($image, $image_url, ['class' => 'fancybox'])}</div>
    </td>
    <td class="dataTableContent  qty-price" valign="top"><div>{Product::getVirtualItemQuantity($product['id'], $product['qty'])}</div></td>
    <td class="dataTableContent table-name-td" valign="top">
        <span style="cursor:pointer" onclick="window.open('{Url::toRoute(['categories/productedit', 'pID' => $product['id']])}')"><b>{$product['name']}</b></span>
        <span class="product-details-model">{TABLE_HEADING_PRODUCTS_MODEL}: <b>{$product['model']} {$gv_state_label}</b></span>
        {if $ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')}
            {$ext::queryOrderProcessAdminOne($product, $iter)}
        {/if}
        {if isset($product['attributes']) && sizeof($product['attributes']) > 0}
            {$manager->render('Attributes', ['product' => $product, 'currency' => $currency, 'currency_value' => $currency_value])}
        {/if}
        {assign var = props value = $app->get('PropsHelper')}{$props::adminOrderProductView($product)}
        {$manager->render('Asset', ['manager' => $manager, 'asset' => $asset])}
        {$manager->render('ProductAssets', ['manager' => $manager, 'product' => $product])}
        {foreach \common\helpers\Hooks::getList('orders/process-order', 'product-name') as $filename}
            {include file=$filename}
        {/foreach}
    </td>
    {if !($order instanceof \common\classes\TmpOrder)}
    <td class="dataTableContent" valign="top">
        <div class="small-margin-bottom">{TEXT_AUTOMATED}: <span id="products-status-{$product['orders_products_id']}" style="color:{$color}">{$status}</span></div>
        {TEXT_MANUAL}: <a href="{Yii::$app->urlManager->createUrl(['orders/products-status-history', 'opID' => $product['orders_products_id']])}" class="right-link"><i class="icon-pencil"></i></a>
        <span id="products-status-manual-{$product['orders_products_id']}" style="color:{if isset($opsmArray[$product['status_manual']])}{$opsmArray[$product['status_manual']]->getColour()}{else}#000000{/if}">
        {if isset($opsmArray[$product['status_manual']])}{$opsmArray[$product['status_manual']]->orders_products_status_manual_name}{/if}
        </span>
    </td>
    <td class="dataTableContent" valign="top">
		{$status_deficit = \common\helpers\OrderProduct::getStockDeficit($product['orders_products_id'])}
		{assign var="status_received" value="`$product['qty_rcvd'] - $product['qty_dspd']`"}
		{assign var="status_dispatched" value="`$product['qty_dspd'] - $product['qty_dlvd']`"}
		{assign var="status_delivered" value="`$product['qty_dlvd']`"}
		{$status_ordered = \common\helpers\OrderProduct::getStockOrdered($product['orders_products_id'])}
		{assign var="status_cancel" value="`$product['qty_cnld']`"}
		<div class="wrapper-row-status">
			<div class="row-status-hide-box">

			</div>
			<div class="row-status-box">
                                {if $status_deficit != 0}
                                    <span class="title-dfct title-order-stock">{$headers['deficit']}:</span>&nbsp;<span id="products-qty-dfct-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $status_deficit)}</span>
                                {/if}
				<div class="row-status hide">
                                    <span class="title-dfct title-order-stock">{$headers['deficit']}:</span>&nbsp;<span id="products-qty-dfct-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $status_deficit)}</span>
				</div>
                                {if $status_received != 0}
                                    <span class="title-rcvd title-order-stock">{$headers['received']}:</span>&nbsp;<span id="products-qty-rcvd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], ($product['qty_rcvd'] - $product['qty_dspd']))}</span>
                                {/if}
				<div class="row-status hide"><span class="title-rcvd title-order-stock">{$headers['received']}:</span>&nbsp;<span id="products-qty-rcvd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], ($product['qty_rcvd'] - $product['qty_dspd']))}</span>
				{if $isTemporary}&nbsp;<a href="{Yii::$app->urlManager->createUrl(['orders/product-allocate-temporary-information', 'opID' => $product['orders_products_id']])}" class="right-link"><i class="icon-warning-sign"></i></a>{/if}
				</div>
                                {if $status_dispatched != 0}
                                    <span class="title-dspd title-order-stock">{$headers['dispatched']}:</span>&nbsp;<span id="products-qty-dspd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], ($product['qty_dspd'] - $product['qty_dlvd']))}</span>
                                {/if}
				<div class="row-status hide">
                                    <span class="title-dspd title-order-stock">{$headers['dispatched']}:</span>&nbsp;<span id="products-qty-dspd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], ($product['qty_dspd'] - $product['qty_dlvd']))}</span>
				</div>
                                {if $status_delivered != 0}
                                    <span class="title-dlvd title-order-stock">{$headers['delivered']}:</span>&nbsp;<span id="products-qty-dlvd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $product['qty_dlvd'])}</span>
                                {/if}
				<div class="row-status hide">
                                    <span class="title-dlvd title-order-stock">{$headers['delivered']}:</span>&nbsp;<span id="products-qty-dlvd-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $product['qty_dlvd'])}</span>
				</div>
                                {if $status_ordered != 0}
                                    <span class="title-ordered title-order-stock">{$headers['ordered']}:</span>&nbsp;<span class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], \common\helpers\OrderProduct::getStockOrdered($product['orders_products_id']))}</span>
                                {/if}
				<div class="row-status hide">
                                    <span class="title-ordered title-order-stock">{$headers['ordered']}:</span>&nbsp;<span class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], \common\helpers\OrderProduct::getStockOrdered($product['orders_products_id']))}</span>
				</div>
                                {if $status_cancel != 0}
                                    <span class="title-cnld title-order-stock">{$headers['cancel']}:</span>&nbsp;<span id="products-qty-cnld-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $product['qty_cnld'])}</span>
                                {/if}
				<div class="row-status hide">
                                    <span class="title-cnld title-order-stock">{$headers['cancel']}:</span>&nbsp;<span id="products-qty-cnld-{$product['orders_products_id']}" class="strong-status-val">{Product::getVirtualItemQuantity($product['id'], $product['qty_cnld'])}</span>
				</div>
			</div>
		</div>
        {if ($ext = \common\helpers\Acl::checkExtensionAllowed('Rma', 'allowed'))}
            {common\extensions\Communication\assets\CommunicationAsset::register($this)|void}
		<div class="return-block">
            <div class="new-return">
                {$ext::getButtonReturnHtml($product['orders_products_id'])}
            </div>
            <div class="returns-list">
                {$ext::getButtonReturnPrintHtml($product['orders_products_id'])}
            </div>
		</div>
        {/if}
    {if count($suppliersPricesArray) > 0}
        {foreach $suppliersPricesArray as $sID => $suppliersPrice}
        <div class="row-status">
        <span class="">{\common\helpers\Suppliers::getSupplierName($sID)}:</span>&nbsp;<span class="strong-status-val">{$suppliersPrice['allocate_received']} x {Formatter::priceEx($suppliersPrice['suppliers_price'], 0, 1, $currency, $currency_value)}</span>
        </div>
        {/foreach}
    {/if}
    </td>
    {/if}
    <td class="dataTableContent" valign="top">{$location}</td>
    <td class="dataTableContent" valign="top">{\common\helpers\Tax::display_tax_value($product['tax'])}%</td>
    <td class="dataTableContent price" valign="top">

        <table class="product-table-price">
            <tr>
                <td></td>
                <td colspan="2" class="vat-info-title vat-exc">{TEXT_EXC_VAT}</td>
            </tr>
            <tr>
                <td rowspan="3" class="product-table-price-qty">
                    <b>{Product::getVirtualItemQuantity($product['id'], $product['qty'])}</b>
                    <span class="multiply-ico">x </span>
                    <span class="brace-ico">{ </span>
                </td>
                <td class="price-right">
                    <b>{Formatter::priceEx((\common\helpers\Product::getVirtualItemQuantityValue($product['id']) * $product['final_price']), $product['tax'], 1, $currency, $currency_value)}</b>

                    {if $product['promo_id'] && \common\helpers\Acl::checkExtensionAllowed('Promotions')}
                        <div class="info-hint">
                            <div class="info-hint-box info-hint-box-left">
                                <div class="info-hint-mustache"></div>
                                {\common\extensions\Promotions\models\PromotionService::getPromoLinkAdmin($product['promo_id'])}
                            </div>
                        </div>
                    {/if}
                    {if $product['discount_description']}
                        <div class="info-hint">
                            <div class="info-hint-box info-hint-box-left">
                                <div class="info-hint-mustache"></div>
                                {\common\helpers\Specials::getLinkAdmin($product['specials_id'], $product['discount_description'])}
                            </div>
                        </div>
                    {/if}
                </td>
                <td class="price-left">
                    <b>{Formatter::priceEx($product['final_price'], $product['tax'], $product['qty'], $currency, $currency_value)}</b>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="vat-info-title vat-inc">{TEXT_INC_VAT}</td>
            </tr>
            <tr>
                <td class="price-right">
                    <b>{Formatter::price((\common\helpers\Product::getVirtualItemQuantityValue($product['id']) * $product['final_price']), $product['tax'], 1, $currency, $currency_value)}</b>
                </td>
                <td class="price-left">
                    <b>{Formatter::price($product['final_price'], $product['tax'], $product['qty'], $currency, $currency_value)}</b>
                </td>
            </tr>
        </table>


    </td>
</tr>
<script>
$(document).ready(function() {
    $('.wrapper-row-status').each(function(){
		var element = $(this).find('.row-status.hide');
		var element_hide = $(this).find('.row-status-hide-box');
		element.appendTo(element_hide);
	});
});
</script>