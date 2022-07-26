tl(createJsUrl('main.js'), function(){

    var topBar = 0; // height of site fixed header bar
    var minItemWidth = 250;
    var bodyDoubleMargin = 50;

    $('.w-catalog-compare').each(function(){
        let url = new URL(window.location.href);
        let currentCategoryId = url.searchParams.get("currentCategoryId") || 0;

        var $compare = $(this);
        var widgetId = $compare.attr('id').substring(4);

        var $items,
            $fixBar,
            $productListing,
            $compareList,
            $compareListWrap,
            $compareListHolder,
            $compareTable,
            $landscapeScroll,
            $landscapeScrollHolder;

        applyCompare($compare);

        if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
            currentCategoryId = entryData.compare.currentCategory.id;
        } else {
            $('.compare', this).hide();
            return;
        }

        tl.subscribe(['productListings', 'compare', 'byCategory'], function(){
            var state = tl.store.getState();
            var sendData = {
                compare: state['productListings']['compare']['byCategory'][currentCategoryId],
                box_id: widgetId,
                currentCategoryId: currentCategoryId
            }
            $.get(getMainUrl() + '/catalog/compare-box', sendData, function(data){
                var state = tl.store.getState();

                var urlPath = window.location.href.split('?')[0];
                urlPath = urlPath + '?' + $.param( {compare: state['productListings']['compare']['byCategory'][currentCategoryId], currentCategoryId: currentCategoryId} );
                window.history.pushState({}, "", urlPath);

                tl.store.dispatch({
                    type: 'ADD_PRODUCTS',
                    value: {
                        products: data.entryData.products,
                    },
                    file: 'boxes/catalog/compare'
                });
                tl.store.dispatch({
                    type: 'PRODUCTS_LISTING_ITEM_ELEMENTS',
                    value: {
                        listingName: data.entryData.widgets['w0']['listingName'],
                        itemElements: data.entryData.productListings[data.entryData.widgets['w0']['listingName']].itemElements,
                    },
                    file: 'boxes/catalog/compare'
                });

                $compare.html(data.html);

                var $productListingStyles = $('#productListingStyles');
                if ($productListingStyles.length > 0){
                    $productListingStyles.remove();
                }
                $('head').append('<style type="text/css" id="productListingStyles">'+data.css+'</style>')

                $('.product-listing .item', $compare).each(function(){
                    ProductListing.applyItem($(this), widgetId);

                    $item = $(this);
                    var productId = $item.data('id');
                    var $checkbox = $('.compare input', $item);
                    var state = tl.store.getState();
                    if (
                        isElementExist(['productListings', 'compare', 'byCategory'], state) &&
                        state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
                    ) {
                        $checkbox.prop('checked', true);
                    } else {
                        $checkbox.removeAttr('checked');
                    }
                });

                ProductListing.productListingCols($('.product-listing', $compare));

                applyCompare($compare);
            }, 'json')
        });

        function applyCompare($compare){
            $items = $('.product-listing .item', $compare);
            $fixBar = $('.compare-fix-bar', $compare);
            $productListing = $('.product-listing', $compare);
            $compareList = $('.compare-list', $compare);
            $compareListWrap = $('.compare-list-wrap', $compare);
            $compareListHolder = $('.compare-list-holder', $compare);
            $compareTable = $('table', $compare);
            $landscapeScroll = $('.landscape-scroll', $compare);
            $landscapeScrollHolder = $('.landscape-scroll-holder', $compare);
            let counts = $items.length;
            var width = Math.floor(100 / (counts + 1));

            $items.css({ width: width + '%'});

            fixingBar();
            $(window).on('scroll', fixingBar);
            compareSize();
            $(window).on('resize', compareSize);
            scrollBar();
            $(window).on('scroll resize', scrollBar);

            $landscapeScroll.on('scroll', function(){
                $fixBar.scrollLeft($landscapeScroll.scrollLeft())
                $compareListWrap.scrollLeft($landscapeScroll.scrollLeft())
            });
            $fixBar.on('scroll', function(){
                $landscapeScroll.scrollLeft($fixBar.scrollLeft())
                $compareListWrap.scrollLeft($fixBar.scrollLeft())
            });
            $compareListWrap.on('scroll', function(){
                $fixBar.scrollLeft($compareListWrap.scrollLeft())
                $landscapeScroll.scrollLeft($compareListWrap.scrollLeft())
            });

            var yesNoDifferent = $('.different-all > div', $compare)
            yesNoDifferent.on('click', function(){
                yesNoDifferent.removeClass('active');
                $(this).addClass('active');
                if ($(this).hasClass('all-property')) {
                    $('tr.same', $compare).show()
                } else {
                    $('tr.same', $compare).hide()
                }
            })
        }

        function createScroll(){
            removeScroll();
            let $items = $('.w-catalog-compare .product-listing .item');
            let counts = $items.length;
            var windowWidth = $(window).width();
            var compareListWidth = $compareList.width();
            var itemWidth = $items.width();
            var mainWidth = (counts + 1) * minItemWidth;

            if (windowWidth - bodyDoubleMargin > compareListWidth) {
                var margin = -(windowWidth - bodyDoubleMargin - compareListWidth) / 2
                $compareList.css({
                    marginLeft: margin,
                    marginRight: margin,
                })
            }
            $productListing.css({width: mainWidth});
            $compareListHolder.css({width: mainWidth});
            $landscapeScrollHolder.css({width: mainWidth});
            if ($(window).width() < 992) {
                $fixBar.css({overflow: 'auto'})
                $compareListWrap.css({overflow: 'auto'})
                $landscapeScroll.css({overflow: 'hidden'})
            } else {
                $fixBar.css({overflow: 'hidden'})
                $compareListWrap.css({overflow: 'hidden'})
                $landscapeScroll.css({overflow: 'auto'})
            }
        }

        function removeScroll() {
            $compareList.css({
                marginLeft: '',
                marginRight: '',
            })
            $productListing.css({width: ''});
            $compareListHolder.css({width: ''});
            $landscapeScrollHolder.css({width: ''});
            $fixBar.css({overflow: ''})
            $compareListWrap.css({overflow: ''})
            $landscapeScroll.css({overflow: ''})
        }

        function compareSize() {
            let $items = $('.w-catalog-compare .product-listing .item');
            let counts = $items.length;

            $compareList.css({
                marginLeft: '',
                marginRight: '',
            })
            removeScroll();
            var windowWidth = $(window).width();
            var compareListWidth = $compareList.width();
            var itemWidth = $items.width();

            if (itemWidth < minItemWidth) {
                if (windowWidth - bodyDoubleMargin > compareListWidth) {
                    if ((minItemWidth - itemWidth) * (counts + 1) < windowWidth - bodyDoubleMargin - compareListWidth) {
                        var margin = -((counts + 1) * minItemWidth - compareListWidth) / 2;
                        $compareList.css({
                            marginLeft: margin,
                            marginRight: margin,
                        })
                    } else {
                        createScroll()
                    }
                } else {
                    createScroll()
                }
            }

            $('.image img', $fixBar).on('load', function(){
                $fixBar.inRow(['.image'], counts);
            });
            $fixBar.inRow(['.image', '.name', '.price', '.description', '.attributes', '.bonusPoints', '.model', '.qtyInput', '.buyButton'], counts);
        }

        function fixingBar(){
            var top = $compareList.offset().top;
            if ($(window).scrollTop() + topBar > top && $(window).scrollTop() + topBar < top + $compareTable.height()){
                $('.model').show();
                $compareList.css({'padding-top': $fixBar.height()});
                $fixBar.css({
                    position: 'fixed',
                    top: topBar,
                    left: $compareList.offset().left,
                    width: $compareList.width(),
                });
                $fixBar.addClass('fix-bar-fix');
                $('.model').hide();
            } else {
                $('.model').show();
                $compareList.css({'padding-top': ''});
                $fixBar.css({
                    position: '',
                    top: '',
                    left: '',
                    width: '',
                });
                $fixBar.removeClass('fix-bar-fix')
            }

        }

        function scrollBar(){
            var left = $compareListWrap.offset().left;
            var width = $compareListWrap.width();

            var compareBottom = $compareListWrap.offset().top*1 + $compareListWrap.height()*1;
            var windowBottom = $(window).scrollTop() + $(window).height()

            $landscapeScroll.css({
                position: '',
                bottom: '',
                left: '',
                width: ''
            })
            if (compareBottom > windowBottom) {
                $landscapeScroll.css({
                    position: 'fixed',
                    bottom: 0,
                    left: left,
                    width: width
                })
            }

        }
    })
})

