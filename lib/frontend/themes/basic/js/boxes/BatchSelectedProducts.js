tl(function(){
    $('.w-batch-selected-products').each(function(){
        var $batchSelectedProducts = $(this);
        var widgetId = $(this).closest('.box').attr('id').substring(4);

        tl.subscribe(['productListings', 'batchSelectedProducts' + widgetId, 'products'], function(){
            var state = tl.store.getState();
            var products = [];
            var stateProducts = state['productListings']['batchSelectedProducts' + widgetId]['products'];
            for (var productId in stateProducts){
                products.push({productId: productId, sortOrder: stateProducts[productId]['sortOrder']});
            }
            products = products.sort(function(a, b){
                return a.sortOrder-b.sortOrder
            }).map(function(item){
                return item.productId;
            });

            var params = {
                id: widgetId,
                products: products
            };
            $.get(getMainUrl() + '/get-widget/one', params, function(html){
                $batchSelectedProducts.html(html);
                $('.item', $batchSelectedProducts).each(function(){
                    var state = tl.store.getState();
                    var $item = $(this);
                    var uprid = $item.data('id');
                    var productId = parseInt(uprid);

                    tl.store.dispatch({
                        type: 'ADD_PRODUCT',
                        value: {
                            id: uprid,
                            product: state.products[productId],
                        },
                        file: 'boxes/ProductListing'
                    });

                    ProductListing.applyItem($(this), widgetId);
                });
                upplyButton()
            })
        });

        function upplyButton(){
            $('.btn-add-products', $batchSelectedProducts).on('click', function(){
                if ( $(this).attr('disabled') ) return false;

                $(this).addClass('loader');
                var state = tl.store.getState();
                var postData = [];
                postData.push({name: '_csrf', value: $('meta[name="csrf-token"]').attr('content')});
                postData.push({name: 'json', value: 1});

                for (var productId in state['productListings']['batchSelectedProducts' + widgetId]['products']) {
                    var productData = state['productListings']['batchSelectedProducts' + widgetId]['products'][productId];

                    postData.push({name: 'qty[]', value: 1});
                    postData.push({name: 'products_id[]', value: productId});
                    if ( productData['attributes'] ) {
                        for(var _id in productData['attributes']){
                            if ( !productData['attributes'].hasOwnProperty(_id) ) continue;
                            postData.push({
                                name: _id.replace('id[', 'id['+productId+']['),
                                value: productData['attributes'][_id]
                            });
                        }
                    }
                }

                $.ajax({
                    url: getMainUrl() + '/index?action=add_all',
                    data: postData,
                    method: 'post'
                }).done(function(data) {
                    window.location.href = getMainUrl() + '/shopping-cart';
                }).always(function(){
                    $(this).removeClass('loader')
                })
            })
        }
    })
})