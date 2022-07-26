tl(function(){
    $('.w-catalog-listing-look').each(function(){

        var state = tl.store.getState();
        if (!isElementExist(['productListings', 'mainListing'], state)) return '';

        var $listingLook = $(this);
        var widgetId = $listingLook.attr('id').substring(4);
        var $listingLookLinks = $('a', $listingLook);

        $listingLookLinks.on('click', function(e){
            $listingLookLinks.removeClass('active');
            $(this).addClass('active');

            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return true
            }

            e.preventDefault();
            var listingId = state.productListings.mainListing;
            var gl = $(this).data('gl');

            var listingType = state['widgets'][listingId][$(this).data('type')]

            tl.store.dispatch({
                type: 'WIDGET_CLEAR_PRODUCTS',
                value: {
                    widgetId: listingId,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'gl',
                    paramValue: gl,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_LISTING_TYPE',
                value: {
                    widgetId: listingId,
                    listingType: listingType,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/ListingLook'
            });
        })
    })
})