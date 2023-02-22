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
        $('.w-catalog-paging').each(function () {

            var $catalogPaging = $(this);
            var state = tl.store.getState();
            var listingId = state.productListings.mainListing;

            var productsOnPage = +state['widgets'][listingId]['productsOnPage'];
            var pageCount = +state['widgets'][listingId]['pageCount'];
            var numberOfProducts = +state['widgets'][listingId]['numberOfProducts'];

            var numberOfPages = Math.ceil(numberOfProducts / productsOnPage);

            if (numberOfPages < 2) {
                $catalogPaging.html('');
                return true;
            }

            var href = '';

            var $paging = $('<div class="paging"></div>');
            if (pageCount == 1) {
                $paging.append('<span class="prev"></span>')
            } else {
                href = setGetParam(window.location.href, 'page', (pageCount - 1));
                $paging.append('<a class="prev" href="'+href+'" data-number="' + (pageCount - 1) + '"></a>')
            }

            var $holder = $('<span class="paging-holder"></span>');
            $paging.append($holder);

            for (var page = 1; page <= numberOfPages; page++) {
                if (pageCount == page) {
                    $holder.append('<span class="paging-item"><span class="page-number active">' + page + '</span></span>')
                } else {
                    href = setGetParam(window.location.href, 'page', page);
                    $holder.append('<span class="paging-item"><a class="page-number" href="'+href+'" data-number="' + page + '">' + page + '</a></span>')
                }
            }

            if (pageCount == numberOfPages) {
                $paging.append('<span class="next"></span>')
            } else {
                href = setGetParam(window.location.href, 'page', (pageCount + 1));
                $paging.append('<a class="next" href="'+href+'" data-number="' + (pageCount + 1) + '"></a>')
            }

            $('> .paging',$catalogPaging).replaceWith($paging);

            $('a', $paging).on('click', function(e){
                e.preventDefault();

                var pageCount = $(this).data('number');

                tl.store.dispatch({
                    type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                    value: {
                        paramName: 'page',
                        paramValue: pageCount,
                    },
                    file: 'boxes/catalog/Paging'
                });
                tl.store.dispatch({
                    type: 'WIDGET_CHANGE_PAGE_COUNT',
                    value: {
                        widgetId: listingId,
                        pageCount: pageCount,
                    },
                    file: 'boxes/catalog/Paging'
                });
                tl.store.dispatch({
                    type: 'WIDGET_PAGE_COUNT_UPDATE_PAGE',
                    value: {
                        widgetId: listingId,
                        pageCountUpdatePage: true,
                    },
                    file: 'boxes/catalog/Paging'
                });
            })

            var initialSlide = Math.floor((pageCount-0.1)/4) * 4;
            if (initialSlide < 0) initialSlide = 0;
            $holder.slick({
                slidesToShow: 4,
                slidesToScroll: 4,
                infinite: false,
                initialSlide: initialSlide,
                appendArrows: $paging
            })
        })
     }
})