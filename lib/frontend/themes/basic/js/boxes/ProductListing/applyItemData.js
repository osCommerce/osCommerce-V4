if (!ProductListing) var ProductListing = {};
ProductListing.applyItemData = function($item, widgetId) {
    var productId = $item.data('id');
    var state = tl.store.getState();

    tl.store.dispatch({
        type: 'WIDGET_PRODUCT_CAN_ADD_TO_CART',
        value: {
            widgetId: widgetId,
            productId: productId,
            canAddToCart: state['products'][productId]['stock_indicator']?state['products'][productId]['stock_indicator']['flags']['can_add_to_cart']:false,
        },
        file: 'boxes/ProductListing/applyItemData'
    });

    tl.store.dispatch({
        type: 'WIDGET_PRODUCT_IN_CART',
        value: {
            widgetId: widgetId,
            productId: productId,
            productInCart: state['products'][productId]['product_in_cart'],
        },
        file: 'boxes/ProductListing/applyItemData'
    });
}