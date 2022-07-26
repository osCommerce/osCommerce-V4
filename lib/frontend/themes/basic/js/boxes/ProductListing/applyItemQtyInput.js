if (!ProductListing) var ProductListing = {};
ProductListing.applyItemQtyInput = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.qtyInput', $item);
    var $input = $('input', $box);

    var state = tl.store.getState();
    var product = state.products[productId];
    if (entryData.GROUPS_DISABLE_CHECKOUT || product.show_attributes_quantity){
        $('> *', $box).hide();
        return '';
    }

    var listingName = state['widgets'][widgetId]['listingName'];
    var itemElements = state['productListings'][listingName]['itemElements'];
    var hasAttributes = +state['products'][productId]['product_has_attributes'];
    var isBundle = +state['products'][productId]['isBundle'];
    if (!itemElements.attributes && hasAttributes || isBundle) {
        $('> *', $box).remove();
        return '';
    }

    tl.subscribe(['widgets', widgetId, 'products', productId, 'canAddToCart'], function(){
        var state = tl.store.getState();
        if (
            !isElementExist(['widgets', widgetId, 'products', productId, 'canAddToCart'], state)
            && !(+state['products'][productId]['is_virtual'])
        ) {
            $box.hide();
        } else {
            $box.show();
        }
    });

    $input.quantity();

    $input.on('change', changeQty);
    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty'], state)) {
        $input.val(state['widgets'][widgetId]['products'][productId]['qty'])
    } else {
        changeQty();
    }

    function changeQty(){
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_QTY',
            value: {
                widgetId: widgetId,
                productId: productId,
                qty: $input.val(),
            },
            file: 'boxes/ProductListing/applyItemQtyInput'
        })
    }

    var $qty_ = [];
    $qty_[0] = $("input[name$='_[0]']", $box);
    if ($qty_[0].length && $qty_[0].val().length) {
        $qty_[0].quantity();
        $qty_[0].on('change', function() { changePackQty(0); });
    }
    $qty_[1] = $("input[name$='_[1]']", $box);
    if ($qty_[1].length && $qty_[1].val().length) {
        $qty_[1].quantity();
        $qty_[1].on('change', function() { changePackQty(1); });
    }
    $qty_[2] = $("input[name$='_[2]']", $box);
    if ($qty_[2].length && $qty_[2].val().length) {
        $qty_[2].quantity();
        $qty_[2].on('change', function() { changePackQty(2); });
    }
    function changePackQty(index){
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_PACK_QTY',
            value: {
                widgetId: widgetId,
                productId: productId,
                qty_: (index + 1),
                qty: $qty_[index].val(),
            },
            file: 'boxes/ProductListing/applyItemQtyInput'
        })
    }
}