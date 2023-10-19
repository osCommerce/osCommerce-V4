tl(createJsUrl('main.js'), function(){
    var params = {};
    params.compare = [];
    var $compareButton = $('.w-catalog-compare-button .compare_button');

    var state = tl.store.getState();
    if (
        isElementExist(['compare', 'currentCategory', 'id'], entryData) &&
        isElementExist(['productListings', 'compare', 'byCategory', entryData.compare.currentCategory.id], state)
    ) {
        params.compare = state.productListings.compare.byCategory[entryData.compare.currentCategory.id]
    }

    $compareButton.on('click', function (e) {
        e.preventDefault();
        window.location = $compareButton.attr('href') + '?' + $.param( params );
    })
    if (params.compare.length > 1) {
        $compareButton.show();
    } else {
        $compareButton.hide();
    }
    //if(params.compare)
        tl.subscribe(['productListings', 'compare'], function(){
            var state = tl.store.getState();

            params.compare = state['productListings']['compare']['byCategory'];
            if (entryData.compare &&
                entryData.compare.currentCategory &&
                entryData.compare.currentCategory.id &&
                params.compare[entryData.compare.currentCategory.id].length > 1
            ) {
                $compareButton.show();
            } else {
                $compareButton.hide();
            }
        });
})