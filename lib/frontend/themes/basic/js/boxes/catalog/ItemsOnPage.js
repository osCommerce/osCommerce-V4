tl(function(){
    $('.w-catalog-items-on-page').each(function(){

        var $listingCount = $(this);
        var widgetId = $listingCount.attr('id').substring(4);
        var $listingCountSelect = $('select', $listingCount);

        $listingCountSelect.on('change', function(e){
            e.preventDefault();
            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return false
            }

            var listingId = state.productListings.mainListing;
            var maxItems = $(this).val();

            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'page',
                    paramValue: 1,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });

            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'max_items',
                    paramValue: maxItems,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PRODUCTS_ON_PAGE',
                value: {
                    widgetId: listingId,
                    productsOnPage: maxItems,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
        })
    })
})