tl(function(){
    $('.w-catalog-b2b-add-button').each(function(){

        var $b2bBox = $(this);
        var widgetId = $b2bBox.attr('id').substring(4);
        var $addButton = $('.add-b2b-products', $b2bBox);

        var state = tl.store.getState();
        if (!state.productListings || !state.productListings.mainListing){
            return true
        }

        var listingId = state.productListings.mainListing;

        if (state['widgets'][listingId]['listingType'] == state['widgets'][listingId]['listingTypeB2b']){
            $b2bBox.show()
        } else {
            $b2bBox.hide()
        }

        tl.subscribe(['widgets', listingId, 'listingType'], function () {
            var state = tl.store.getState();
            if (state['widgets'][listingId]['listingType'] == state['widgets'][listingId]['listingTypeB2b']){
                $b2bBox.show()
            } else {
                $b2bBox.hide()
            }
        });


        $addButton.on('click', function(){
            $(this).addClass('loader')
            var state = tl.store.getState();
            var postData = [];
            postData.push({name: '_csrf', value: $('meta[name="csrf-token"]').attr('content')});
            postData.push({name: 'json', value: 1});
            for (var productId in state['widgets'][listingId]['products']) {

                postData.push({name: 'qty[]', value: state.widgets[listingId]['products'][productId]['qty']});
                postData.push({name: 'products_id[]', value: productId});

                if (isElementExist(['widgets', listingId, 'products', productId, 'qty_'], state)) {
                    var qty_ = state.widgets[listingId]['products'][productId]['qty_'];
                    for (var index in qty_){
                        postData.push({name: 'qty_[' + productId + '][' + (index - 1) + ']', value: qty_[index]});
                    }
                }
                if (isElementExist(['widgets', listingId, 'products', productId, 'attributes'], state)) {
                    var attributes = state.widgets[listingId]['products'][productId]['attributes'];
                    for (var attrKey in attributes){
                        postData.push({name: attrKey, value: attributes[attrKey]});
                    }
                }
                if (isElementExist(['widgets', listingId, 'products', productId, 'mixAttributes'], state)) {
                    var attributes = state.widgets[listingId]['products'][productId]['mixAttributes'];
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

            }


            $.ajax({
                url: getMainUrl() + '?action=add_all',
                data: postData,
                method: 'post',
                //dataType: 'json'
            })
                .done(function(data) {

                    window.location.href = getMainUrl() + '/shopping-cart';
                })
        })


    })
})