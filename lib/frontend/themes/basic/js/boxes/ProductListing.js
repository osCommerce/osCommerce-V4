tl(createJsUrl('main.js'), function(){
    $('.product-listing').each(applyListing);

    $('body').on('applyListing', '.product-listing', applyListing);

    tl.subscribe(['productListings', 'href'], function(){
        var state = tl.store.getState();
        window.history.pushState("", "", state.productListings.href);
    })

    if (localStorage.compareByCategory) {
        tl.store.dispatch({
            type: 'UPDATE_COMPARE',
            value: JSON.parse(localStorage.compareByCategory),
            file: 'boxes/ProductListing'
        });
        $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/update-compare', {
            compare: JSON.parse(localStorage.compareByCategory),
        })
    }

    ProductListing.fbl();

    function applyListing(){

        var state = tl.store.getState();
        var $listing = $(this);

        var widgetId = $listing.closest('.box').attr('id').substring(4);

        $('.item', $listing).each(function(){
            ProductListing.applyItem($(this), widgetId);
        });

        tl.subscribe(['widgets', widgetId, 'listingType'], function(){
            updateProducts($listing, widgetId)
        });
        tl.subscribe(['widgets', widgetId, 'listingSorting'], function(){
            updateProducts($listing, widgetId)
        });
        tl.subscribe(['widgets', widgetId, 'productsOnPage'], function(){
            updateProducts($listing, widgetId)
        });
        tl.subscribe(['widgets', widgetId, 'pageCountUpdatePage'], function(){
            var state = tl.store.getState();
            if (state['widgets'][widgetId]['pageCountUpdatePage']) {
                tl.store.dispatch({
                    type: 'WIDGET_PAGE_COUNT_UPDATE_PAGE',
                    value: {
                        widgetId: widgetId,
                        pageCountUpdatePage: false,
                    },
                    file: 'boxes/catalog/Paging'
                });
                updateProducts($listing, widgetId)
            }
        });

        if (state.productListings && state.productListings.mainListing && widgetId == state.productListings.mainListing) {
            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF',
                value: {
                    href: window.location.href,
                },
                file: 'boxes/ProductListing'
            });
        }

        /*if (localStorage.wishlist) {
            var wishlistProducts = JSON.parse(localStorage.wishlist);
            if (isElementExist(['productListings', 'wishlist', 'products'], state)) {
                Object.assign(wishlistProducts, state['productListings']['wishlist']['products'])
            }
            tl.store.dispatch({
                type: 'UPDATE_PRODUCTS_IN_LIST',
                value: {
                    listingName: 'wishlist',
                    products: wishlistProducts,
                },
                file: 'boxes/ProductListing'
            });
        }*/

        ProductListing.productListingCols($listing);
        layoutChange($listing, widgetId);
    }

    function updateProducts($listing, widgetId){
        var listingName = $listing.data('listing-name');
        var sendData = {};
        var url = entryData.mainUrl;
        var state = tl.store.getState();

        if (listingName === 'cart') {
            url = url + 'cart/index'
        }
        if (state.productListings && state.productListings.mainListing && widgetId == state.productListings.mainListing) {
            url = state.productListings.href
        }

        sendData.productListing = 1;
        sendData.onlyProducts = 1;

        $listing.addClass('loader');

        $.ajax({
            url: url,
            data: sendData,
            dataType: 'json'
        })
            .done(function(data) {
                $listing.removeClass('loader');
                var state = tl.store.getState();

                var listingType = state['widgets'][widgetId]['listingType'];
                $listing.attr('data-listing-type', listingType);
                var listingClasses = $listing.attr('class');
                listingClasses = listingClasses.replace(/(\sw-list-)([a-zA-Z0-9\-\_]+)/, '$1' + listingType);
                listingClasses = listingClasses.replace(/(\slist-)([a-zA-Z0-9\-\_]+)/, '$1' + listingType);

                $listing.attr('class', listingClasses);

                tl.store.dispatch({
                    type: 'ADD_PRODUCTS',
                    value: {
                        products: data.entryData.products,
                    },
                    file: 'boxes/ProductListing'
                });
                tl.store.dispatch({
                    type: 'PRODUCTS_LISTING_ITEM_ELEMENTS',
                    value: {
                        listingName: data.entryData.widgets['w0']['listingName'],
                        itemElements: data.entryData.productListings[data.entryData.widgets['w0']['listingName']].itemElements,
                    },
                    file: 'boxes/ProductListing'
                });
                //Object.assign(entryData, data.entryData);
                $listing.html(data.html);
                $(window).scrollTop($listing.offset().top - 100)

                tl.store.dispatch({
                    type: 'WIDGET_CHANGE_NUMBER_OF_PRODUCTS',
                    value: {
                        widgetId: widgetId,
                        numberOfProducts: data.entryData.widgets['w0']['numberOfProducts'],
                    },
                    file: 'boxes/ProductListing'
                });

                var $productListingStyles = $('#productListingStyles');
                if ($productListingStyles.length > 0){
                    $productListingStyles.remove();
                }
                $('head').append('<style type="text/css" id="productListingStyles">'+data.css+'</style>')

                $('.item', $listing).each(function(){
                    ProductListing.applyItem($(this), widgetId);
                });

                ProductListing.productListingCols($listing);
            })
    }

    function layoutChange($listing, widgetId) {
        var state = tl.store.getState();
        if (
            !isElementExist(['widgets', widgetId, 'colInRow'], state) ||
            !isElementExist(['widgets', widgetId, 'colInRowCarousel'], state)
        ) return false;

        $(window).on('layoutChange', function(event, d){
            if (state['widgets'][widgetId]['colInRowCarousel'][d.to]){
                ProductListing.productListingCols($listing);
            }
        })
    }

    ProductListing.carousel();
});
