if (!ProductListing) var ProductListing = {};
ProductListing.applyItemWishlistButton = function($item, widgetId) {
    var productId = $item.data('id');
    var $checkbox = $('.wishlistButton input', $item);
    var $wishlistAdd = $('.wishlistButton label', $item);

    var state = tl.store.getState();

    var listingName = state['widgets'][widgetId]['listingName'];
    var itemElements = state['productListings'][listingName]['itemElements'];
    var hasAttributes = +state['products'][productId]['product_has_attributes'];
    var isBundle = +state['products'][productId]['isBundle'];
    if (!itemElements.attributes && hasAttributes || isBundle) {
        $wishlistAdd.hide();
        return '';
    }

    if (isElementExist(['products', productId, 'show_attributes_quantity'], state)) {
        $wishlistAdd.hide();
    }

    checkBox();
    tl.subscribe(['productListings', 'wishList', 'products'], checkBox);
    tl.subscribe(['widgets', widgetId, 'products', productId, 'attributes'], checkBox);

    $checkbox.on('change', function(e){
        var state = tl.store.getState();

        if (state.account.isGuest){
            $checkbox.prop('checked', false);
            //$wishlistAdd.hide();
            var $accountDropdown = $('.w-account .account-title:first').clone();
            $('> a', $accountDropdown).remove();
            $('> ul', $accountDropdown).css({
                display: 'block',
                position: 'static',
                margin: '0 auto',
                border: 'none'
            });
            $('form', $accountDropdown).on('submit', function(e){
                e.preventDefault();
                $.post($(this).attr('action'), $(this).serializeArray(), function(){
                    window.location.reload()
                })
            })
            alertMessage($accountDropdown, 'w-account login-popup')
            return;
        }

        var uprid = productId;

        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            //var uprid = helpers.getUprid(productId, state['widgets'][widgetId]['products'][productId]['attributes'])
        }


        var postData = [];
        postData.push({name: 'qty', value: 1});
        postData.push({name: 'products_id', value: productId});

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

        if ($checkbox.prop('checked')){

            $.post(getMainUrl() + '/personal-catalog/add', postData, function (d) {
                alertMessage(d.message);
            });
            tl.store.dispatch({
                type: 'ADD_PRODUCT_IN_LIST',
                value: {
                    listingName: 'wishList',
                    productId: uprid,
                },
                file: 'boxes/ProductListing/applyItemWishlistButton'
            });
        } else {

            $.post(getMainUrl() + '/personal-catalog/confirm-delete', postData, function (d) {
                alertMessage(d.message);
            });

        }

    })

    function checkBox(){
        var state = tl.store.getState();
        var uprid = productId;

        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            var uprid = helpers.getUprid(productId, state['widgets'][widgetId]['products'][productId]['attributes'])
        }

        if (isElementExist(['productListings', 'wishList', 'products', uprid], state)) {
            $checkbox.prop('checked', true)
        } else {
            $checkbox.prop('checked', false)
        }
    }
}