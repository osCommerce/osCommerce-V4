if (!ProductListing) var ProductListing = {};
ProductListing.applyItemCompare = function($item, widgetId) {
    var productId = $item.data('id');
    var $checkbox = $('.compare input', $item);
    var $viewButton = $('.compare .view', $item);
    var params = {};
    params.compare = [];

    var currentCategoryId = 0
    if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
        currentCategoryId = entryData.compare.currentCategory.id;
    } else {
        var url = new URL(window.location.href);
        currentCategoryId = url.searchParams.get("currentCategoryId");
        if (!currentCategoryId) {
            $('.compare', $item).hide();
            return;
        }
    }

    changeCompare();
    tl.subscribe(['productListings', 'compare', 'byCategory'], changeCompare);

    function changeCompare(){
        var state = tl.store.getState();
        if (isElementExist(['productListings', 'compare', 'byCategory'], state)) {
            params.compare = state.productListings.compare.byCategory;
        }

        if (
            isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state) &&
            state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
        ) {
            $checkbox.prop('checked', true);

            if (state['productListings']['compare']['byCategory'][currentCategoryId].length > 1) {
                $viewButton.show()
            } else {
                $viewButton.hide()
            }
        } else {
            $checkbox.removeAttr('checked');
            $viewButton.hide();
        }
    }

    /*$viewButton.popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCompare'><div class='pop-up-close'></div><div class='popup-heading compare-head'>" + entryData.tr.BOX_HEADING_COMPARE_LIST + "</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
        data: params
    })*/

    $viewButton.on('click', function (e) {
        var state = tl.store.getState();
        if (isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state)) {
            params.compare = state['productListings']['compare']['byCategory'][currentCategoryId];
            params.currentCategoryId = currentCategoryId
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
                    productId: productId,
                    categoryId: currentCategoryId || 0,
                },
                file: 'boxes/ProductListing/applyItemCompare'
            });
            updateCompare('add-to-compare', productId)

        } else {
            tl.store.dispatch({
                type: 'REMOVE_FROM_COMPARE',
                value: {
                    productId: productId,
                    categoryId: currentCategoryId,
                },
                file: 'boxes/ProductListing/applyItemCompare'
            });
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
}
