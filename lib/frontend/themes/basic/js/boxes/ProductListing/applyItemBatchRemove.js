if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBatchRemove = function($item, widgetId) {
    var productId = $item.data('id');
    $('.btn-batch-remove', $item).on('click', function(){
        tl.store.dispatch({
            type: 'REMOVE_PRODUCT_FROM_BATCH',
            value: {
                productId: productId,
                widgetId: widgetId,
            },
            file: 'boxes/ProductListing/applyItemBatchRemove'
        });
    });
}