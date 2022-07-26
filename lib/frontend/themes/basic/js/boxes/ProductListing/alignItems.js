if (!ProductListing) var ProductListing = {};
ProductListing.alignItems = function($listing) {
    var widgetId = $listing.closest('.box').attr('id').substring(4);
    var state = tl.store.getState();
    if (!isElementExist(['widgets', widgetId, 'colInRow'], state)) return false;

    $('.image img', $listing).on('load', function(){
        $listing.inRow(['.image'], state['widgets'][widgetId]['productListingCols']);
    });

    $listing.inRow(
        ['.image', '.name', '.price', '.description', '.attributes', '.bonusPoints', '.model', '.qtyInput', '.buyButton', '.productGroup'],
        state['widgets'][widgetId]['productListingCols']
    );
}
