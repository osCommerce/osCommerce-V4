<div class="wb-or-prod edit_product_popup edit_product_popup1">
<form name="cart_quantity" action="{\Yii::$app->urlManager->createUrl($queryParams)}" method="post" id="product-form">
    <input type="hidden" name="currentCart" value="{$currentCart}">
    <div class="widget box box-no-shadow" style="border: none;">
        <div class="popup-heading">{$smarty.const.T_EDIT_PROD}</div>
        <div class="popup-content">
			<div >
               {$manager->render('Product', ['product' => $product, 'manager' => $manager, 'edit' => true])}
            </div>								
        </div>
        {tep_draw_hidden_field('action', 'add_products')}
		<div class="popup-buttons">
            <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
            <div class="btn-center"><span class="btn btn-default btn-reset" >{$smarty.const.TEXT_RESET}</span></div>
            <div><input type="submit" class="btn btn-confirm btn-save" value="{$smarty.const.IMAGE_SAVE}"></div>
		</div>		

    </div>
</form>
<script>
    
    order.activate_plus_minus('.edit_product_popup');

    
    $('form[name=cart_quantity]').submit(function(e){
        if (checkproducts([ { 'product':product } ])){
            var params = [];
            params.push({ 'name': 'action', 'value': 'add_products'});
            params = params.concat(product.getProducts());
            
            $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", params, function(data){
                if (data.status == 'ok'){
                    window.location.reload();
                } else if (data.hasOwnProperty('message')) {
                    order.showMessage(data.message, true);                        
                }
            }, 'json');
            
        }
        return false;
    })

    entryData.tr.TEXT_EXC_VAT = '{$smarty.const.TEXT_EXC_VAT}';
    entryData.tr.TEXT_INC_VAT = '{$smarty.const.TEXT_INC_VAT}';
    entryData.tr.QUANTITY_DISCOUNT_DIFFERENT = '{$smarty.const.QUANTITY_DISCOUNT_DIFFERENT}';
    entryData.tr.ATTRIBUTE_PRICE_DIFFERENT = '{$smarty.const.ATTRIBUTE_PRICE_DIFFERENT}';
    entryData.tr.TEXT_CHANGE_TO = '{$smarty.const.TEXT_CHANGE_TO}';
    entryData.tr.TEXT_LEAVE = '{$smarty.const.TEXT_LEAVE}';

    getOrderRates = function(){
        var rates = [];
        {foreach $rates as $key => $rate}
            rates['{$key}'] = '{$rate}';
        {/foreach}
        return rates;
    }
    var product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', '{$product.id}', {if $product['is_pack']}true{else}false{/if}, false, $('.edit-product .product-details:last'));
    product.edit = true;
    product.products_id = "{\common\helpers\Inventory::normalize_id($product['id'])}";
    product.newDetails.name = "{$product['name']}";
    product.oldDetails.name = "{$product['products_name']}";
    
    {if is_array($product['attributes']) && count($product['attributes']) > 0}
        {foreach $product['attributes'] as $key => $value}
            product.oldDetails.attributes.push({ '{$key}':{$value} });
        {/foreach}
    {/if}
    product.oldDetails.selected_rate = "{$product['overwritten']['tax_selected']}";
    {if $product['gift_wrap_price']}
    product.gift_wrap_price = {$product['gift_wrap_price']};    
    {/if}
    
    product.getDetails();
    
    manualEdit = function(obj){
        product.manualEdit(obj);
    }
    
    changeTax = function(obj){
        product.changeTax();
    }
    changeConfTax  = function(obj){
        product.changeConfTax(obj);
    }
    
    getDetails = function(obj, reProd){        
        product.getDetails(obj, reProd);
    }
    {if $product['is_configurator']}
        product.getDetails();
    {/if}
    
    $('body .edit_product_popup').on('change', '.new-product', function(e){
        product.checkQuantity();
        product.getDetails(e.target);
    })
    
    $('.btn-reset').click(function(){
        $.get('{$currentUrl}', function (response) {
            $('.pop-up-content:last').html(response)
        })
    })



</script>
</div>