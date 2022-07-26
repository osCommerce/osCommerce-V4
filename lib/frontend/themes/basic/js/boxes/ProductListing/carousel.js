if (!ProductListing) var ProductListing = {};
ProductListing.carousel = function() {
    tl(createJsUrl('slick.min.js'), function(){
        $('.products-listing').each(applyListing);

        $('body').on('applyListing', '.product-listing', applyListing);

        function applyListing(){
            var $listing = $(this);

            var widgetId = $listing.closest('.box').attr('id').substring(4);
            var state = tl.store.getState();

            if (!isElementExist(['widgets', widgetId, 'viewAs'], state) ||
                !state['widgets'][widgetId]['viewAs'] === 'carousel'
            ) return '';

            $listing.parent().css('position', 'relative');

            var tabs = $listing.parents('.tabs');
            tabs.find('> .block').show();
            var responsive = [];

            for (var size in state['widgets'][widgetId]['colInRowCarousel']) {
                responsive.push({
                    breakpoint: size,
                    settings: {
                        slidesToShow: +state['widgets'][widgetId]['colInRowCarousel'][size],
                        slidesToScroll: +state['widgets'][widgetId]['colInRowCarousel'][size]
                    }
                })
            }

            $listing.slick({
                slidesToShow: +state['widgets'][widgetId]['productListingCols'],
                slidesToScroll: +state['widgets'][widgetId]['productListingCols'],
                infinite: false,
                responsive: responsive
            });

            setTimeout(function(){ tabs.trigger('tabHide') }, 100)
        }
    })
}