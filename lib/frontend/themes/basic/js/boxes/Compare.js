tl(createJsUrl('slick.min.js'), function(){
    tl.subscribe(['productListings', 'compare'], compare);
    $('.main-content').on('updateContent', compare)

    var slickData = {
        slidesToShow: 4,
        slidesToScroll: 4,
        responsive: [
            {
                breakpoint: 1100,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 700,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            },
        ]
    };

    compare();

    function compare(){
        var state = tl.store.getState();
        var $compareList = $('<div class="compare-list-holder"></div>');
        $('.compare-list').html('').append($compareList);

        var compareProducts = [];
        if (
            isElementExist(['compare', 'currentCategory', 'id'], entryData) &&
            isElementExist(['productListings', 'compare', 'byCategory', entryData.compare.currentCategory.id], state)
        ) {
            compareProducts = state.productListings.compare.byCategory[entryData.compare.currentCategory.id]
        }

        if (compareProducts.length > 0) {
            $compareList.show();
            $compareList.html('');

            var $compareListButtons = $('<div class="compare-list-buttons"></div>');
            $compareList.append($compareListButtons);
            var $compareListProducts = $('<div class="compare-list-products"></div>');
            $compareList.append($compareListProducts);

            var products = compareProducts;
            $compareListProducts.append(products.map(productItem));
            $compareListProducts.slick(slickData);
            var noProducts = products.filter(function(productId){
                if (
                    state.products &&
                    state.products[productId] &&
                    state.products[productId].price &&
                    state.products[productId].products_name &&
                    state.products[productId].image &&
                    state.products[productId].link
                ) {
                    return false;
                } else {
                    return true;
                }
            });
            if (noProducts.length > 0) {
                $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/get-products', {productIds: products}, function(response) {
                    tl.store.dispatch({
                        type: 'ADD_PRODUCTS',
                        value: {
                            products: response,
                        },
                        file: 'boxes/Compare.js'
                    });

                    $compareListProducts.slick('unslick');
                    $compareListProducts.html('');
                    $compareListProducts.append(products.map(productItem));
                    $compareListProducts.slick(slickData);
                }, 'json');
            }


            if (compareProducts.length > 1) {
                var $clearCompareButton = $('<div class="clear-compare-button">' + entryData.tr.TEXT_CLEANING_ALL + '</div>');
                $compareListButtons.append($clearCompareButton);
                var $compareButton = $('<div class="btn compare-button">' + entryData.tr.BOX_HEADING_COMPARE_LIST + '</div>');
                $compareListButtons.append($compareButton);

                var params = {};
                params.compare = [];
                params.compare = products;
                $compareButton.on('click', function (e) {
                    window.location = state['productListings']['compare']['compareUrl'] + '?' + $.param(params);
                });
                $clearCompareButton.on('click', function (e) {
                    tl.store.dispatch({
                        type: 'UPDATE_COMPARE',
                        value: {},
                        file: 'boxes/ProductListing/applyItemCompare'
                    });
                    localStorage.setItem('compareByCategory', JSON.stringify([]))
                    $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/clear-compare')
                })
            }
        } else {
            $compareList.hide();
            $compareList.html('');
        }
    }

    function productItem(productId) {
        var state = tl.store.getState();
        if (
            !state.products || !state.products[productId] ||
            !state.products[productId].price ||
            !state.products[productId].products_name ||
            !state.products[productId].image ||
            !state.products[productId].link
        ) {
            return '';
        }
        var product = state.products[productId];
        var $product = $('<div class="item"></div>');
        var $itemHolder = $('<div class="item-holder"></div>');

        var $image = $('<div class="image"><a href="' + product.link + '"><img src="' + product.image + '"></a></div>');
        var $name = $('<div class="name"><a href="' + product.link + '">' + product.products_name + '</a></div>');

        var $price = $('<div class="price">' + (product.price && product.price.current ? product.price.current : '') + '</div>');
        if (product.price && product.price.special) {
            $price = $(`<div class="price">
                            <span class="old">${product.price.old}</span>
                            <span class="special">${product.price.special}</span>
                        </div>`);
        }
        if (product.please_login) {
            $price = $('<div class="price pl_price">' + product.please_login + '</div>');
        }
        var $remove = $('<div class="remove"></div>');

        $product.append($itemHolder);
        $itemHolder.append($image);
        $itemHolder.append($name);
        $itemHolder.append($price);
        $itemHolder.append($remove);

        $remove.on('click', function(){
            if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
                tl.store.dispatch({
                    type: 'REMOVE_FROM_COMPARE',
                    value: {
                        productId: productId,
                        categoryId: entryData.compare.currentCategory.id,
                    },
                    file: 'boxes/ProductListing/applyItemCompare'
                });
            }
            var state = tl.store.getState();
            localStorage.setItem('compareByCategory', JSON.stringify(state['productListings']['compare']['byCategory']));

            $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/remove-from-compare', {
                productId: productId,
                categoryId: entryData.compare.currentCategory.id || 0
            }, function(response){
                if (!entryData.compare.currentCategory.id) {
                    tl.store.dispatch({
                        type: 'UPDATE_COMPARE',
                        value: response,
                        file: 'boxes/ProductListing/applyItemCompare'
                    });
                    localStorage.setItem('compareByCategory', JSON.stringify(response))
                }
            }, 'json')
        });

        return $product
    }
});