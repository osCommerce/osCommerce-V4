if (!ProductListing) var ProductListing = {};
ProductListing.applyItemPrice = function($item, widgetId) {
    var productId = $item.data('id');
    tl.subscribe(['widgets', widgetId, 'products', productId, 'price'], function(){
        var state = tl.store.getState();
        var price = {};
        if (isElementExist(['widgets', widgetId, 'products', productId, 'price'], state)){
            price = state['widgets'][widgetId]['products'][productId]['price']
        } else if (isElementExist(['products', productId, 'price'], state)){
            price = state['products'][productId]['price']
        }
        if (price){
            if (price.old && price.special){
                $('.price .current', $item).hide().html('')
                $('.price .old', $item).show().html(price.old)
                $('.price .special', $item).show().html(price.special)
            } else if (price.current) {
                $('.price .current', $item).show().html(price.current)
                $('.price .old', $item).hide('')
                $('.price .special', $item).hide('')
            }
        }
    })
}