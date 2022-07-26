if (!ProductListing) var ProductListing = {};

ProductListing.applyItemStock = function($item, widgetId) {
    var productId = $item.data('id');
    tl.subscribe(['widgets', widgetId, 'products', productId, 'stock_indicator'], function(){
        var state = tl.store.getState();
        var stock_indicator = {};
        if (isElementExist(['widgets', widgetId, 'products', productId, 'stock_indicator'], state)){
            stock_indicator = state.widgets[widgetId]['products'][productId]['stock_indicator']
        }
        if (stock_indicator && stock_indicator.stock_code && stock_indicator.stock_indicator_text){
            $('.stock', $item).html(`
            <span class="${stock_indicator.stock_code}">
                <span class="${stock_indicator.stock_code}-icon">&nbsp;</span>
                ${stock_indicator.stock_indicator_text}
            </span>
            `)
        }
    })
}