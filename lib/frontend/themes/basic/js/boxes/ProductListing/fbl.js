if (!ProductListing) var ProductListing = {};
ProductListing.fbl = function() {
    var state = tl.store.getState();
    var listingId = state.productListings.mainListing;
    if (!isElementExist(['widgets', listingId, 'fbl'], state)) return false;

    var $listing = $('#box-'+listingId+' .products-listing');

    var url = state.productListings.href;
    var sentRequest = false;
    var sendData = {};
    var pageCount = state['widgets'][listingId]['pageCount'];
    var allPages = Math.ceil( state['widgets'][listingId]['numberOfProducts'] / state['widgets'][listingId]['productsOnPage']);
    sendData.productListing = 1;
    sendData.onlyProducts = 1;

    tl.subscribe(['widgets', listingId, 'pageCount'], function(){
        var state = tl.store.getState();
        sentRequest = false;
        pageCount = state['widgets'][listingId]['pageCount'];
    });
    tl.subscribe(['widgets', listingId, 'numberOfProducts'], function(){
        var state = tl.store.getState();
        allPages = Math.ceil( state['widgets'][listingId]['numberOfProducts'] / state['widgets'][listingId]['productsOnPage']);
    });
    tl.subscribe(['widgets', listingId, 'productsOnPage'], function(){
        var state = tl.store.getState();
        allPages = Math.ceil( state['widgets'][listingId]['numberOfProducts'] / state['widgets'][listingId]['productsOnPage']);
    });
    tl.subscribe(['productListings', 'href'], function(){
        var state = tl.store.getState();
        url = state.productListings.href;
    });

    var listingPosition = JSON.parse(localStorage.getItem('listing-position')) || {};
    if (listingPosition.url === window.location.href && listingPosition.page > 1) {
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PAGE_COUNT',
            value: {
                widgetId: listingId,
                pageCount: listingPosition.page,
            },
            file: 'boxes/ProductListing'
        });
        let requests = [];
        for (let i = 1; i <= listingPosition.page; i++) {
            $listing.addClass('loader');
            sendData.page = i;
            requests.push($.ajax({
                url: url,
                data: sendData,
                dataType: 'json'
            }))
        }
        Promise.all(requests).then(function () {
            requests.forEach(function(data){
                tl.store.dispatch({
                    type: 'ADD_PRODUCTS',
                    value: {
                        products: data.responseJSON.entryData.products,
                    },
                    file: 'boxes/ProductListing'
                });

                var $newItems = $('<div>' + data.responseJSON.html + '</div>');
                var $items = $('.item', $newItems);

                $items.each(function(){
                    ProductListing.applyItem($(this), listingId);
                });

                $listing.append($items);

                ProductListing.alignItems($listing);
                $(window).scrollTop(listingPosition.scrollTop);
            });

            $listing.removeClass('loader');

            pageCount = listingPosition.page;
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: listingPosition.page,
                },
                file: 'boxes/ProductListing'
            });
        })
    }

    $(window).on('scroll', function(){
        localStorage.setItem('listing-position', JSON.stringify({
            url: window.location.href,
            page: pageCount,
            scrollTop: $(window).scrollTop()
        }));

        if (
            $listing.height() - $(window).scrollTop() < $(window).height() &&
            !sentRequest &&
            pageCount < allPages
        ) {
            var state = tl.store.getState();
            sentRequest = true;
            sendData.page = pageCount + 1;

            $.ajax({
                url: url,
                data: sendData,
                dataType: 'json'
            })
                .done(function(data) {
                    sentRequest = true;

                    tl.store.dispatch({
                        type: 'ADD_PRODUCTS',
                        value: {
                            products: data.entryData.products,
                        },
                        file: 'boxes/ProductListing'
                    });

                    var $newItems = $('<div>' + data.html + '</div>');
                    var $items = $('.item', $newItems);

                    $items.each(function(){
                        ProductListing.applyItem($(this), listingId);
                    });

                    tl.store.dispatch({
                        type: 'WIDGET_CHANGE_PAGE_COUNT',
                        value: {
                            widgetId: listingId,
                            pageCount: pageCount + 1,
                        },
                        file: 'boxes/ProductListing'
                    });

                    $listing.append($items);

                    ProductListing.alignItems($listing)
                })
        }
    })
}