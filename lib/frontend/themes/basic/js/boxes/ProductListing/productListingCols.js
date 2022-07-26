if (!ProductListing) var ProductListing = {};
ProductListing.productListingCols = function($listing) {
    var widgetId = $listing.closest('.box').attr('id').substring(4);
    var state = tl.store.getState();

    var productListingCols = 1

    if (state['widgets'][widgetId]['listingType'] === state['widgets'][widgetId]['listingTypeCol'] || !state['widgets'][widgetId]['listingTypeCol']) {
        if (isElementExist(['widgets', widgetId, 'colInRow'], state)) {
            productListingCols = state['widgets'][widgetId]['colInRow'];
        }

        if (isElementExist(['widgets', widgetId, 'colInRowCarousel'], state)) {
            var currentSize = $(window).width();
            var difference = 10000;
            var findSize = 0;
            for (var size in state['widgets'][widgetId]['colInRowCarousel']) {
                if (0 < (size - currentSize) && (size - currentSize) < difference){
                    difference = findSize - currentSize;
                    findSize = size
                }
            }
            if (findSize) {
                productListingCols = state['widgets'][widgetId]['colInRowCarousel'][findSize];
            }
        }

    }

    tl.store.dispatch({
        type: 'WIDGET_CHANGE_SETTINGS',
        value: {
            widgetId: widgetId,
            settingName: 'productListingCols',
            settingValue: productListingCols,
        },
        file: 'boxes/ProductListing/productListingCols'
    });

    var listingClasses = $listing.attr('class');
    listingClasses = listingClasses.replace(/(\scols-)([0-9])/, '$1' + productListingCols)
    $listing.attr('class', listingClasses);
    ProductListing.alignItems($listing);
}
