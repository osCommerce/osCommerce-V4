if (!ProductListing) var ProductListing = {};
ProductListing.addProductToCart = function(widgetId, productId){
    var state = tl.store.getState();

    var qty;
    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty'], state)) {
        qty = state.widgets[widgetId]['products'][productId]['qty']
    } else {
        qty = 1;
    }

    tl.store.dispatch({
        type: 'WIDGET_ADDING_PRODUCT_TO_LIST',
        value: {
            widgetId: widgetId,
            productId: productId,
            list: 'cart',
        },
        file: 'boxes/ProductListing'
    });

    var postData = [];
    postData.push({name: 'action', value: 'buy_now'});
    postData.push({name: 'qty', value: qty});
    postData.push({name: 'products_id', value: productId});

    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty_'], state)) {
        var qty_ = state.widgets[widgetId]['products'][productId]['qty_'];
        for (var index in qty_){
            postData.push({name: 'qty_[' + (index - 1) + ']', value: qty_[index]});
        }
    }
    if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
        var attributes = state.widgets[widgetId]['products'][productId]['attributes'];
        for (var attrKey in attributes){
            postData.push({name: attrKey, value: attributes[attrKey]});
        }
    }
    if (isElementExist(['widgets', widgetId, 'products', productId, 'mixAttributes'], state)) {
        var attributes = state.widgets[widgetId]['products'][productId]['mixAttributes'];
        for (var attributeId in attributes){
            for (var optionId in attributes[attributeId]) {
                if (attributes[attributeId][optionId]) {
                    postData.push({name: 'mix_attr[' + productId + '][]['+attributeId+']', value: optionId});
                    postData.push({name: 'mix[]', value: productId});
                    postData.push({name: 'mix_qty[' + productId + '][]', value: attributes[attributeId][optionId]});
                }
            }
        }
    }

    postData.push({name: '_csrf', value: $('meta[name="csrf-token"]').attr('content')});
    postData.push({name: 'json', value: 1});

    $.ajax({
        url: getMainUrl() + '/?action=add_product',
        data: postData,
        method: 'post',
        dataType: 'json'
    })
        .done(function(data) {
			var state = tl.store.getState();
            if (data.error) {                
                window.location.href = state['products'][productId]['link'];
            }

            var products = {};

            data.products.forEach(function(prod){
                products[prod.stock_products_id] = {
                    id: prod.id,
                    qty: prod.quantity
                }
            });

            tl.store.dispatch({
                type: 'WIDGET_NO_ADDING_PRODUCT_TO_LIST',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    list: 'cart',
                },
                file: 'boxes/ProductListing/addProductToCart'
            });
            tl.store.dispatch({
                type: 'UPDATE_PRODUCTS_IN_LIST',
                value: {
                    listingName: 'cart',
                    products: products,
                },
                file: 'boxes/ProductListing/addProductToCart'
            });

            tl.store.dispatch({
                type: 'WIDGET_PRODUCT_IN_CART',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    productInCart: true,
                },
                file: 'boxes/ProductListing/addProductToCart'
            });

            if(isElementExist(['widgets', widgetId, 'showPopup'], state) && state['widgets'][widgetId]['showPopup']){
				window.location.href = getMainUrl() + '/shopping-cart';
			}else{
				$('<a href="' + getMainUrl() + '/shopping-cart?popup=1" data-class="cart-popup"></a>').popUp().trigger('click');
			}
        })
        .fail(function() {
            tl.store.dispatch({
                type: 'WIDGET_NO_ADDING_PRODUCT_TO_LIST',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    list: 'cart',
                },
                file: 'boxes/ProductListing/addProductToCart'
            });
        });

}