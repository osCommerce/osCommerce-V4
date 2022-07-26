tl(createJsUrl('slick.min.js'), function(){

    var state = tl.store.getState();
    if (!isElementExist(['productListings', 'mainListing'], state)) return '';

    createPaging();

    var listingId = state.productListings.mainListing;
    tl.subscribe(['widgets', listingId, 'pageCount'], function(){
        createPaging()
    });
    tl.subscribe(['widgets', listingId, 'productsOnPage'], function(){
        createPaging()
    });
    tl.subscribe(['widgets', listingId, 'numberOfProducts'], function(){
        createPaging()
    });

    function createPaging(){
        var $catalogCounts = $('.w-catalog-counts-items');
        var $fromNum = $('.from-num', $catalogCounts);
        var $toNum = $('.to-num', $catalogCounts);
        var $numberOfRows = $('.number-of-rows', $catalogCounts);
        var state = tl.store.getState();
        var listingId = state.productListings.mainListing;

        var productsOnPage = +state['widgets'][listingId]['productsOnPage'];
        var pageCount = +state['widgets'][listingId]['pageCount'];
        var numberOfProducts = +state['widgets'][listingId]['numberOfProducts'];

        var numberOfPages = Math.ceil(numberOfProducts / productsOnPage);

        if (numberOfPages < 2) {
            $catalogCounts.html('');
            return true;
        }

        var from = productsOnPage * (pageCount - 1) + 1;
        var to = productsOnPage * (pageCount - 1) + productsOnPage;
        if (to > numberOfProducts) to = numberOfProducts;

        $fromNum.html(from);
        $toNum.html(to);
        $numberOfRows.html(numberOfProducts);
     }
})