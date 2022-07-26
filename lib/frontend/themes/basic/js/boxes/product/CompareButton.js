tl(function() {
    $('.main-content').on('updateContent', applyCompareButton)
    applyCompareButton()

    function applyCompareButton(){
        $('.w-product-compare-button').each(function(){
            var $buttonWidget = $(this);

            var productId = +$('input[name="products_id"]').val();
            var $checkbox = $('input', $buttonWidget);
            var $viewButton = $('.view', $buttonWidget);
            var params = {};
            params.compare = [];

            var currentCategoryId = 0
            if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
                currentCategoryId = entryData.compare.currentCategory.id;
            }

            tl.subscribe(['productListings', 'compare', 'byCategory'], function(){
                var state = tl.store.getState();
                params.compare = state['productListings']['compare']['byCategory'];

                if (
                    isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state) &&
                    state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
                ) {
                    $buttonWidget.prop('checked', true);

                    if (state['productListings']['compare']['byCategory'][currentCategoryId].length > 1) {
                        $viewButton.show()
                    } else {
                        $viewButton.hide()
                    }
                } else {
                    $checkbox.prop('checked', false);
                    $viewButton.hide()
                }
            });

            $viewButton.on('click', function (e) {
                var state = tl.store.getState();
                if (isElementExist(['productListings', 'compare', 'byCategory'], state)) {
                    params.compare = state['productListings']['compare']['byCategory']
                }
                e.preventDefault();
                window.location = $viewButton.attr('href') + '?' + $.param( params );
            })

            $checkbox.on('change', function(){
                var qty = 0;
                if ($checkbox.prop('checked')) {

                    tl.store.dispatch({
                        type: 'ADD_TO_COMPARE',
                        value: {
                            productId: +productId,
                            categoryId: +currentCategoryId || 0,
                        },
                        file: 'boxes/ProductListing/applyItemCompare'
                    });
                    updateCompare('add-to-compare', productId)

                } else {
                    if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
                        tl.store.dispatch({
                            type: 'REMOVE_FROM_COMPARE',
                            value: {
                                productId: +productId,
                                categoryId: +currentCategoryId,
                            },
                            file: 'boxes/ProductListing/applyItemCompare'
                        });
                    }
                    updateCompare('remove-from-compare', productId)
                }

                var state = tl.store.getState();
                localStorage.setItem('compareByCategory', JSON.stringify(state['productListings']['compare']['byCategory']))
            })

            function updateCompare(action, productId){
                $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/' + action, {
                    productId: productId,
                    categoryId: currentCategoryId || 0
                }, function(response){
                    if (!currentCategoryId) {
                        tl.store.dispatch({
                            type: 'UPDATE_COMPARE',
                            value: response,
                            file: 'boxes/ProductListing/applyItemCompare'
                        });
                        localStorage.setItem('compareByCategory', JSON.stringify(response))
                    }
                }, 'json')
            }
        });



        $('.comparePr').each(function(){
            var $buttonWidget = $(this);

            var productId = +$('input[name="products_id"]').val();

            var currentCategoryId = 0;
            if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
                currentCategoryId = entryData.compare.currentCategory.id;
            }

            var state = tl.store.getState();
            if (
                isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state) &&
                state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
            ) {
                $buttonWidget.addClass('checked');
            } else {
                $buttonWidget.removeClass('checked');
            }

            tl.subscribe(['productListings', 'compare', 'byCategory'], function(){
                var state = tl.store.getState();

                if (
                    isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state) &&
                    state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
                ) {
                    $buttonWidget.addClass('checked');
                } else {
                    $buttonWidget.removeClass('checked');
                }
            });

            $buttonWidget.on('click', function(){

                if (!$buttonWidget.hasClass('checked')) {

                    tl.store.dispatch({
                        type: 'ADD_TO_COMPARE',
                        value: {
                            productId: +productId,
                            categoryId: +currentCategoryId || 0,
                        },
                        file: 'boxes/ProductListing/applyItemCompare'
                    })

                } else {
                    if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
                        tl.store.dispatch({
                            type: 'REMOVE_FROM_COMPARE',
                            value: {
                                productId: +productId,
                                categoryId: +currentCategoryId,
                            },
                            file: 'boxes/ProductListing/applyItemCompare'
                        })
                    }
                }

                var state = tl.store.getState();
                localStorage.setItem('compareByCategory', JSON.stringify(state['productListings']['compare']['byCategory']))
            });
        })
    }
});