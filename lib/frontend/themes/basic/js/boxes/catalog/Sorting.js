tl(function(){
    $('.w-catalog-sorting').each(function(){

        var $listingSorting = $(this);
        var widgetId = $listingSorting.attr('id').substring(4);
        var $listingSortingSelect = $('select', $listingSorting);

        $listingSortingSelect.on('change', function(e){
            e.preventDefault();
            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return false
            }

            var listingId = state.productListings.mainListing;
            var sort = $(this).val();

            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/Sorting'
            });
            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'sort',
                    paramValue: sort,
                },
                file: 'boxes/catalog/Sorting'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_LISTING_SORTING',
                value: {
                    widgetId: listingId,
                    listingSorting: sort,
                },
                file: 'boxes/catalog/Sorting'
            });
        })
    })
})