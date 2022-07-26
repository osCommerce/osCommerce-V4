if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBatchSelect = function($item, widgetId) {
    var productId = $item.data('id');
    var $checkbox = $('.batchSelect input', $item);
    var state = tl.store.getState();
    if (!isElementExist(['widgets', widgetId, 'batchSelectedWidget'], state)) {
        $('.batchSelect', $item).hide();
        return '';
    }

    var batchSelectedWidgetId = state['widgets'][widgetId]['batchSelectedWidget'];

    //checkUncheck();
    tl.subscribe(['productListings', 'batchSelectedProducts' + batchSelectedWidgetId, 'products'], checkUncheck);
    tl.subscribe(['widgets', widgetId, 'products', productId, 'attributes'], checkUncheck);

    $checkbox.on('change', function(){
        var state = tl.store.getState();
        var uprid = productId;
        var attributes = false;
        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            attributes = state['widgets'][widgetId]['products'][productId]['attributes'];
            uprid = helpers.getUprid(productId, attributes);
        }

        if ($checkbox.prop('checked')) {
            tl.store.dispatch({
                type: 'ADD_PRODUCT_TO_BATCH',
                value: {
                    productId: uprid,
                    attributes: attributes,
                    widgetId: batchSelectedWidgetId
                },
                file: 'boxes/ProductListing/applyItemBatchSelect'
            });
        } else {
            tl.store.dispatch({
                type: 'REMOVE_PRODUCT_FROM_BATCH',
                value: {
                    productId: uprid,
                    widgetId: batchSelectedWidgetId
                },
                file: 'boxes/ProductListing/applyItemBatchSelect'
            });
        }
    }).trigger('change');

    function checkUncheck(){
        var state = tl.store.getState();

        var uprid = productId;
        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            uprid = helpers.getUprid(productId, state['widgets'][widgetId]['products'][productId]['attributes'])
        }
        if (isElementExist(['productListings', 'batchSelectedProducts' + batchSelectedWidgetId, 'products', uprid], state)) {
            $checkbox.prop('checked', true);
        } else {
            $checkbox.removeAttr('checked');
        }
    }
}