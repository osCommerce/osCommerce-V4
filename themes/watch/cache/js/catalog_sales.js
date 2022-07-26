!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?t(exports):"function"==typeof define&&define.amd?define(["exports"],t):t((e=e||self).Redux={})}(this,function(e){"use strict";var t=function(e){var t,r=e.Symbol;return"function"==typeof r?r.observable?t=r.observable:(t=r("observable"),r.observable=t):t="@@observable",t}("undefined"!=typeof self?self:"undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof module?module:Function("return this")()),r=function(){return Math.random().toString(36).substring(7).split("").join(".")},n={INIT:"@@redux/INIT"+r(),REPLACE:"@@redux/REPLACE"+r(),PROBE_UNKNOWN_ACTION:function(){return"@@redux/PROBE_UNKNOWN_ACTION"+r()}};function o(e,t){var r=t&&t.type;return"Given "+(r&&'action "'+r+'"'||"an action")+', reducer "'+e+'" returned undefined. To ignore an action, you must explicitly return the previous state. If you want this reducer to hold no value, you can return null instead of undefined.'}function i(e,t){return function(){return t(e.apply(this,arguments))}}function u(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function c(e,t){var r=Object.keys(e);return Object.getOwnPropertySymbols&&r.push.apply(r,Object.getOwnPropertySymbols(e)),t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),r}function a(e){for(var t=1;arguments.length>t;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?c(r,!0).forEach(function(t){u(e,t,r[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):c(r).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))})}return e}function f(){for(var e=arguments.length,t=Array(e),r=0;e>r;r++)t[r]=arguments[r];return 0===t.length?function(e){return e}:1===t.length?t[0]:t.reduce(function(e,t){return function(){return e(t.apply(void 0,arguments))}})}e.__DO_NOT_USE__ActionTypes=n,e.applyMiddleware=function(){for(var e=arguments.length,t=Array(e),r=0;e>r;r++)t[r]=arguments[r];return function(e){return function(){var r=e.apply(void 0,arguments),n=function(){throw Error("Dispatching while constructing your middleware is not allowed. Other middleware would not be applied to this dispatch.")},o={getState:r.getState,dispatch:function(){return n.apply(void 0,arguments)}},i=t.map(function(e){return e(o)});return a({},r,{dispatch:n=f.apply(void 0,i)(r.dispatch)})}}},e.bindActionCreators=function(e,t){if("function"==typeof e)return i(e,t);if("object"!=typeof e||null===e)throw Error("bindActionCreators expected an object or a function, instead received "+(null===e?"null":typeof e)+'. Did you write "import ActionCreators from" instead of "import * as ActionCreators from"?');var r={};for(var n in e){var o=e[n];"function"==typeof o&&(r[n]=i(o,t))}return r},e.combineReducers=function(e){for(var t=Object.keys(e),r={},i=0;t.length>i;i++){var u=t[i];"function"==typeof e[u]&&(r[u]=e[u])}var c,a=Object.keys(r);try{!function(e){Object.keys(e).forEach(function(t){var r=e[t];if(void 0===r(void 0,{type:n.INIT}))throw Error('Reducer "'+t+"\" returned undefined during initialization. If the state passed to the reducer is undefined, you must explicitly return the initial state. The initial state may not be undefined. If you don't want to set a value for this reducer, you can use null instead of undefined.");if(void 0===r(void 0,{type:n.PROBE_UNKNOWN_ACTION()}))throw Error('Reducer "'+t+"\" returned undefined when probed with a random type. Don't try to handle "+n.INIT+' or other actions in "redux/*" namespace. They are considered private. Instead, you must return the current state for any unknown actions, unless it is undefined, in which case you must return the initial state, regardless of the action type. The initial state may not be undefined, but can be null.')})}(r)}catch(e){c=e}return function(e,t){if(void 0===e&&(e={}),c)throw c;for(var n=!1,i={},u=0;a.length>u;u++){var f=a[u],s=e[f],d=(0,r[f])(s,t);if(void 0===d){var l=o(f,t);throw Error(l)}i[f]=d,n=n||d!==s}return n?i:e}},e.compose=f,e.createStore=function e(r,o,i){var u;if("function"==typeof o&&"function"==typeof i||"function"==typeof i&&"function"==typeof arguments[3])throw Error("It looks like you are passing several store enhancers to createStore(). This is not supported. Instead, compose them together to a single function.");if("function"==typeof o&&void 0===i&&(i=o,o=void 0),void 0!==i){if("function"!=typeof i)throw Error("Expected the enhancer to be a function.");return i(e)(r,o)}if("function"!=typeof r)throw Error("Expected the reducer to be a function.");var c=r,a=o,f=[],s=f,d=!1;function l(){s===f&&(s=f.slice())}function p(){if(d)throw Error("You may not call store.getState() while the reducer is executing. The reducer has already received the state as an argument. Pass it down from the top reducer instead of reading it from the store.");return a}function h(e){if("function"!=typeof e)throw Error("Expected the listener to be a function.");if(d)throw Error("You may not call store.subscribe() while the reducer is executing. If you would like to be notified after the store has been updated, subscribe from a component and invoke store.getState() in the callback to access the latest state. See https://redux.js.org/api-reference/store#subscribe(listener) for more details.");var t=!0;return l(),s.push(e),function(){if(t){if(d)throw Error("You may not unsubscribe from a store listener while the reducer is executing. See https://redux.js.org/api-reference/store#subscribe(listener) for more details.");t=!1,l();var r=s.indexOf(e);s.splice(r,1)}}}function y(e){if(!function(e){if("object"!=typeof e||null===e)return!1;for(var t=e;null!==Object.getPrototypeOf(t);)t=Object.getPrototypeOf(t);return Object.getPrototypeOf(e)===t}(e))throw Error("Actions must be plain objects. Use custom middleware for async actions.");if(void 0===e.type)throw Error('Actions may not have an undefined "type" property. Have you misspelled a constant?');if(d)throw Error("Reducers may not dispatch actions.");try{d=!0,a=c(a,e)}finally{d=!1}for(var t=f=s,r=0;t.length>r;r++)(0,t[r])();return e}return y({type:n.INIT}),(u={dispatch:y,subscribe:h,getState:p,replaceReducer:function(e){if("function"!=typeof e)throw Error("Expected the nextReducer to be a function.");c=e,y({type:n.REPLACE})}})[t]=function(){var e,r=h;return(e={subscribe:function(e){if("object"!=typeof e||null===e)throw new TypeError("Expected the observer to be an object.");function t(){e.next&&e.next(p())}return t(),{unsubscribe:r(t)}}})[t]=function(){return this},e},u},Object.defineProperty(e,"__esModule",{value:!0})});

var reducers={};


/* Start file "modules/window-sizes" */
var tlSize = {
    current: [],
    dimensions: [],

    init: function(){
        tlSize.dimensions = entryData.layoutSizes;
        $(window).on('layoutChange', tlSize.bodyClass);
        tlSize.resize();
        $(window).on('resize', tlSize.resize);
    },

    resize: function(){
        $.each(tlSize.dimensions, function(key, val){
            var from = val[0]*1;
            var to = val[1];
            if (to) {
                to = to*1
            } else {
                to = 10000
            }
            var data = { };
            var w = window.innerWidth;
            if (!w) {
                w = $(window).width();
            }
            if (from <= w && w <= to) {
                if ($.inArray(key, tlSize.current ) === -1) {
                    tlSize.current.push(key);
                    tlSize.current = tlSize.sort(tlSize.current);
                    data = {
                        key: key,
                        status: 'in',
                        from: from,
                        to: to,
                        current: tlSize.current
                    };
                    $(window).trigger('layoutChange', [data]);
                    $(window).trigger(key+'in', [data]);
                }
            } else {
                var index = tlSize.current.indexOf(key);
                if (index > -1) {
                    tlSize.current.splice(index, 1);
                    tlSize.current = tlSize.sort(tlSize.current);
                    data = {
                        key: key,
                        status: 'out',
                        from: from,
                        to: to,
                        current: tlSize.current
                    };
                    $(window).trigger('layoutChange', [data]);
                    $(window).trigger(key+'out', [data]);
                }
            }
        })
    },

    sort: function(arr){
        var v = [];
        var t = [];
        var tmp = [];
        var l = arr.length;
        for (var i = 0; i < l; i++) {
            tmp[i] = '0w0';
            $.each(arr, function (key, val) {
                v = val.split('w');
                v[0] = v[0]*1;
                v[1] = v[1]*1;
                if (!v[1]) {
                    v[1] = 10000
                }
                t = tmp[i].split('w');
                t[0] = t[0]*1;
                t[1] = t[1]*1;
                if (t[1] < v[1]) {
                    tmp[i] = val
                } else if (t[1] == v[1] && t[0] > v[0]) {
                    tmp[i] = val
                }
            });
            var index = arr.indexOf(tmp[i]);
            arr.splice(index, 1);
        }

        return tmp
    },

    bodyClass: function(e, d){
        if (d.status == 'in') {
            $('body').addClass(d.key)
        }
        if (d.status == 'out') {
            $('body').removeClass(d.key)
        }
    }

};
/* End file "modules/window-sizes" */


/* Start file "modules/tl-init" */
if (!Object.assign) {
    Object.defineProperty(Object, 'assign', {
        enumerable: false,
        configurable: true,
        writable: true,
        value: function(target, firstSource) {
            'use strict';
            if (target === undefined || target === null) {
                throw new TypeError('Cannot convert first argument to object');
            }

            var to = Object(target);
            for (var i = 1; i < arguments.length; i++) {
                var nextSource = arguments[i];
                if (nextSource === undefined || nextSource === null) {
                    continue;
                }

                var keysArray = Object.keys(Object(nextSource));
                for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
                    var nextKey = keysArray[nextIndex];
                    var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
                    if (desc !== undefined && desc.enumerable) {
                        to[nextKey] = nextSource[nextKey];
                    }
                }
            }
            return to;
        }
    });
}

tl.reducers = {};
function tl_action(script) {
    if (typeof jQuery == 'function') {
        tl_start = true;
        var action = function (block) {
            var key = true;
            $.each(block.js, function (j, js) {
                var include_index = tl_include_js.indexOf(js);
                if (include_index == -1 || tl_include_loaded.indexOf(js) == -1) {
                    key = false;
                }
            });
            if (key && block && typeof block.script === "function") {
                if (typeof requestIdleCallback === "function"){
                    requestIdleCallback(block.script);
                } else {
                    block.script()
                }
            }
            return key
        };
        $.each(script, function (i, block) {
            if (!action(block)) {
                $.each(block.js, function (j, js) {
                    var include_index = tl_include_js.indexOf(js);
                    if (include_index == -1) {
                        tl_include_js.push(js);
                        include_index = tl_include_js.indexOf(js);
                        $.ajax({
                            url: js, success: function () {
                                tl_include_loaded.push(js);
                                $(window).trigger('tl_action_' + include_index);
                            },
                            error: function (a, b, c) {
                                console.error('Error: "' + js + '" ' + c);
                            },
                            dataType: 'script',
                            cache: true
                        });
                    }
                    $(window).on('tl_action_' + include_index, function () {
                        action(block)
                    })
                })
            }
        })
    } else {
        setTimeout(function () {
            tl_action(script)
        }, 100)
    }
    document.cookie = "xwidth="+window.outerWidth;
    document.cookie = "xheight="+window.outerHeight;
};

tl(createJsUrl('main.js'), function(){
    $('.footerTitle, .gift-code .heading-4').click(function(){
        if($(window).width() >= 720) return;
        $(this).toggleClass('active');
        $('~ *', this).slideToggle();
    });
});

/* End file "modules/tl-init" */


/* Start file "reducers/account" */
tl.reducers.account = function(state, actions){
    if (!state) state = entryData.account;
    if (!state) state = [];

    var newState ='';

    switch (actions.type) {
        case 'LOGGED_IN':
            newState = JSON.parse(JSON.stringify(state));

            newState = actions.value.account;

            return newState;
        default:
            return state
    }
}
/* End file "reducers/account" */


/* Start file "reducers/themeSettings" */
tl.reducers.themeSettings = function(state, actions){
    if (!state) state = entryData.themeSettings;
    if (!state) state = [];

    var newState ='';

    switch (actions.type) {
        case 'CHANGE_THEME_SETTING':
            newState = JSON.parse(JSON.stringify(state));

            newState = actions.value.account;

            return newState;
        default:
            return state
    }
}
/* End file "reducers/themeSettings" */


/* Start file "reducers/filters" */
tl.reducers.filters = function(state, actions){
    if (!state) state = entryData.filters;
    if (!state) state = [];

    var newState ='';

    switch (actions.type) {
        case 'FILTERS_CHANGE':
            newState = JSON.parse(JSON.stringify(state));

            newState = actions.value.filters;

            return newState;
        default:
            return state
    }
}
/* End file "reducers/filters" */


/* Start file "boxes/ProductListing" */
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

/* End file "boxes/ProductListing" */


/* Start file "boxes/ProductListing/applyItemData" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemData = function($item, widgetId) {
    var productId = $item.data('id');
    var state = tl.store.getState();

    tl.store.dispatch({
        type: 'WIDGET_PRODUCT_CAN_ADD_TO_CART',
        value: {
            widgetId: widgetId,
            productId: productId,
            canAddToCart: state['products'][productId]['stock_indicator']['flags']['can_add_to_cart'],
        },
        file: 'boxes/ProductListing/applyItemData'
    });

    tl.store.dispatch({
        type: 'WIDGET_PRODUCT_IN_CART',
        value: {
            widgetId: widgetId,
            productId: productId,
            productInCart: state['products'][productId]['product_in_cart'],
        },
        file: 'boxes/ProductListing/applyItemData'
    });
}
/* End file "boxes/ProductListing/applyItemData" */


/* Start file "boxes/ProductListing/applyItemImage" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemImage = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.image', $item);
    var state = tl.store.getState();
    tl(createJsUrl('jquery.lazy.min.js'), function(){
        $('.image img', $item).lazy({
            bind: 'event',
            beforeLoad: function(){
                $('source', $item).each(function(){
                    let srcset = $(this).data('srcset');
                    $(this).attr('srcset', srcset).removeAttr('data-srcset')
                })
            }
        });
    });

    if (!isElementExist(['widgets', widgetId, 'listingName'], state) ||
        !isElementExist(['widgets', widgetId, 'products'], state)) {
        return;
    }
    const listingName = state.widgets[widgetId].listingName;
    if (!isElementExist(['productListings', listingName, 'itemElementSettings', 'image', 'add_images'], state)) {
        return;
    }

    const $holder = $('<div class="image-holder"></div>');

    const p1 = new Promise((resolve, reject) => {
        $.get('catalog/product-images', { id: productId }, function(responce){
            for (let imageKey in responce) {
                if (isElementExist([imageKey, 'image', 'Medium', 'url'], responce)) {
                    $holder.append(`<div class="item-image"><div><img src="${responce[imageKey].image.Medium.url}"></div></div>`)
                }
            }
            $box.html('').append($holder)
            resolve()
        }, 'json')
    });

    const p2 = new Promise((resolve, reject) => {
        tl(createJsUrl('slick.min.js'), function(){
            resolve()
        })
    })

    Promise.all([p1, p2]).then(values => {
        $holder.slick({dots: true})
    });
};
/* End file "boxes/ProductListing/applyItemImage" */


/* Start file "boxes/ProductListing/applyItemPrice" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemPrice = function($item, widgetId) {
    var productId = $item.data('id');
    tl.subscribe(['widgets', widgetId, 'products', productId, 'price'], function(){
        var state = tl.store.getState();
        var price = {};
        if (isElementExist(['widgets', widgetId, 'products', productId, 'price'], state)){
            price = state['widgets'][widgetId]['products'][productId]['price']
        } else if (isElementExist(['products', productId, 'price'], state)){
            price = state['products'][productId]['price']
        }
        if (price){
            if (price.old && price.special){
                $('.price .current', $item).hide().html('')
                $('.price .old', $item).show().html(price.old)
                $('.price .special', $item).show().html(price.special)
            } else if (price.current) {
                $('.price .current', $item).show().html(price.current)
                $('.price .old', $item).hide('')
                $('.price .special', $item).hide('')
            }
        }
    })
}
/* End file "boxes/ProductListing/applyItemPrice" */


/* Start file "boxes/ProductListing/applyItemStock" */
if (!ProductListing) var ProductListing = {};

ProductListing.applyItemStock = function($item, widgetId) {
    var productId = $item.data('id');
    tl.subscribe(['widgets', widgetId, 'products', productId, 'stock_indicator'], function(){
        var state = tl.store.getState();
        var stock_indicator = {};
        if (isElementExist(['widgets', widgetId, 'products', productId, 'stock_indicator'], state)){
            stock_indicator = state.widgets[widgetId]['products'][productId]['stock_indicator']
        }
        if (stock_indicator && stock_indicator.stock_code && stock_indicator.stock_indicator_text){
            $('.stock', $item).html(`
            <span class="${stock_indicator.stock_code}">
                <span class="${stock_indicator.stock_code}-icon">&nbsp;</span>
                ${stock_indicator.stock_indicator_text}
            </span>
            `)
        }
    })
}
/* End file "boxes/ProductListing/applyItemStock" */


/* Start file "boxes/ProductListing/applyItemAttributes" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemAttributes = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.attributes', $item);
    var $inputs = $('input[type="radio"], select', $box);
    var $qty = $('.qty-inp', $box);
    var attributes = {};
    var state = tl.store.getState();
    if (isElementExist(['widgets', widgetId, 'batchSelectedWidget'], state)) {
        $box.find('input[type="radio"], select').each(function(){
            $(this).attr('name', (this.name.indexOf('list')===0?this.name:('list'+this.name))/*.replace(/\[/,'['+parseInt(productId)+'][')*/);
        });
    }
    $inputs.serializeArray().forEach(function(element){
        attributes[element.name] = element.value
    });

    $qty.quantity();

    tl.store.dispatch({
        type: 'WIDGET_CLEAR_PRODUCT_MIX_ATTRIBUTE',
        value: {
            widgetId: widgetId,
            productId: productId,
        },
        file: 'boxes/ProductListing/applyItemAttributes',
    })

    $qty.on('change', function(){
        var attributeId = $(this).closest('.mix-attributes').data('id');
        var optionId = $(this).closest('.attribute-qty-block').data('id');
        var qty = $(this).val();
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_MIX_ATTRIBUTE',
            value: {
                widgetId: widgetId,
                productId: productId,
                attributeId: attributeId,
                optionId: optionId,
                qty: qty,
            },
            file: 'boxes/ProductListing/applyItemAttributes',
        })
    });

    $inputs.on('change', function(){
        $box.addClass('loader');
        var data = {}
        $inputs.serializeArray().forEach(function(element){
            attributes[element.name] = element.value
            data[element.name] = element.value
        })

        var state = tl.store.getState();
        data.products_id = productId;
        data.qty = state.widgets[widgetId]['products'][productId]['qty'];
        data.type = 'productListing';

        ProductListing.updateAttributes(widgetId, productId, data, $item);
    });

    tl.store.dispatch({
        type: 'WIDGET_CHANGE_PRODUCT_ATTRIBUTE',
        value: {
            widgetId: widgetId,
            productId: productId,
            attributes: attributes,
        },
        file: 'boxes/ProductListing/applyItemAttributes',
    })
}
/* End file "boxes/ProductListing/applyItemAttributes" */


/* Start file "boxes/ProductListing/applyItemBuyButton" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBuyButton = function($item, widgetId){
    var productId = $item.data('id');
    var $buyBox = $('.buyButton', $item);
    var $btnBuy = $('.btn-buy', $buyBox);
    var $btnPreloader = $('.btn-preloader', $buyBox);
    var $btnChooseOptions = $('.btn-choose-options', $buyBox);
    var $btnInCart = $('.btn-in-cart', $buyBox);
    var $loadedQty = $('.loaded-qty', $buyBox);
    var $btnNotify = $('.btn-notify', $buyBox);
    var $btnNotifyForm = $('.btn-notify-form', $buyBox);

    var state = tl.store.getState();
    var product = state.products[productId];
    if (entryData.GROUPS_DISABLE_CHECKOUT){
        $buyBox.hide().html('');
        return '';
    }

    var listingName = state['widgets'][widgetId]['listingName'];
    var itemElements = state['productListings'][listingName]['itemElements'];
    var hasAttributes = +state['products'][productId]['product_has_attributes'];
    var isBundle = +state['products'][productId]['isBundle'];
    if (!itemElements.attributes && hasAttributes || isBundle) {
        $btnChooseOptions.show();
        $btnBuy.hide();
        $btnInCart.hide();
        return '';
    }

    canAddToCart();
    tl.subscribe(['widgets', widgetId, 'products', productId, 'canAddToCart'], function(){
        canAddToCart()
    });

    switchBuyButton();
    tl.subscribe(['widgets', widgetId, 'products', productId, 'productInCart'], function(){
        switchBuyButton()
    });
    tl.subscribe(['products', productId, 'stock_indicator', 'flags'], function(){
        switchBuyButton()
    });

    tl.subscribe(['widgets', widgetId, 'products', productId, 'addingToCart'], function(){
        state = tl.store.getState()
        if (
            isElementExist( ['widgets', widgetId, 'products', productId, 'addingToCart'], state)
            && state['widgets'][widgetId]['products'][productId]['addingToCart']
        ){
            $btnInCart.addClass('hide');
            $btnBuy.addClass('hide');
            $btnPreloader.show();
        } else {
            $btnInCart.removeClass('hide');
            $btnBuy.removeClass('hide');
            $btnPreloader.hide();
        }
    });

    //loadedQty();
    tl.subscribe(['productListings', 'cart', 'products', productId, 'qty'], function(){
        loadedQty()
    });

    $btnBuy.on('click', function(e){
        e.preventDefault();
        var state = tl.store.getState()
        if (
            +product.product_has_attributes &&
            !isElementExist( ['widgets', widgetId, 'products', productId, 'attributes'], state)
        ){
            window.location.href = product.link
        }
        ProductListing.addProductToCart(widgetId, productId)
    });

    $btnNotifyForm.on('click', function(){
        const $form = $(`
                <form>
                    <div class="middle-form">
                        <div class="heading-3">${entryData.tr.BACK_IN_STOCK}</div>
                        <div class="col-full">
                            <label>
                                ${entryData.tr.TEXT_NAME}
                                <input type="text" class="notify-name">
                            </label>
                        </div>
                        <div class="col-full">
                            <label>
                                ${entryData.tr.ENTRY_EMAIL_ADDRESS}
                                <input type="text" class="notify-email">
                            </label>
                        </div>
                        <div class="center-buttons">
                          <button type="submit" class="btn">${entryData.tr.NOTIFY_ME}</button>
                        </div>
                    </div>
                </form>`)
        alertMessage($form, 'notify-form');

        $form.on('submit', function(){
            if ($('.notify-name', $form).val() < entryData.tr.ENTRY_FIRST_NAME_MIN_LENGTH) {
                alertMessage(entryData.tr.NAME_IS_TOO_SHORT.replace('%s', entryData.tr.ENTRY_FIRST_NAME_MIN_LENGTH));
            } else {
                var email = $(".notify-email", $form).val();
                if (!isValidEmailAddress(email)) {
                    alertMessage(entryData.tr.ENTER_VALID_EMAIL);
                } else {
                    $.ajax({
                        url: getMainUrl() + '/catalog/product-notify',
                        data: {
                            name: $('.notify-name', $form).val(),
                            email: email,
                            products_id: productId,
                        },
                        success: function(msg) {
                            $form.html('<div>' + msg + '</div>');
                        }
                    });
                }
            }
            return false;
        })
    })

    function loadedQty(){
        state = tl.store.getState()
        if (
            isElementExist( ['productListings', 'cart', 'products', productId, 'qty'], state)
            && state['productListings']['cart']['products'][productId]['qty'] > 0
        ){
            $loadedQty.show();
            $('span', $loadedQty).html(state['productListings']['cart']['products'][productId]['qty'])
        } else {
            $loadedQty.hide();
        }
    }

    function switchBuyButton(){
        var state = tl.store.getState();
        if (
            isElementExist(['widgets', widgetId, 'products', productId, 'productInCart'], state) &&
            !isElementExist( ['themeSettings', 'showInCartButton'], state)
        ) {
            $btnInCart.show();
            $btnBuy.hide();
        } else {
            $btnInCart.hide();
            if (isElementExist(['products', productId, 'stock_indicator', 'flags', 'add_to_cart'], state)) {
                $btnBuy.show();
            } else {
                $btnBuy.hide();
            }
        }
        if (isElementExist(['products', productId, 'stock_indicator', 'flags', 'notify_instock'], state)) {
            if (hasAttributes) {
                $btnChooseOptions.show();
            } else {
                $btnNotify.show();
            }
        } else {
            $btnNotify.hide();
        }
    }

    function canAddToCart(){
        var state = tl.store.getState();
        if (
            !isElementExist(['widgets', widgetId, 'products', productId, 'canAddToCart'], state)
            && !(+state['products'][productId]['is_virtual'])
        ) {
            $buyBox.hide();
        } else {
            $buyBox.show();
        }
    }
}
/* End file "boxes/ProductListing/applyItemBuyButton" */


/* Start file "boxes/ProductListing/applyItemQtyInput" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemQtyInput = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.qtyInput', $item);
    var $input = $('input', $box);

    var state = tl.store.getState();
    var product = state.products[productId];
    if (entryData.GROUPS_DISABLE_CHECKOUT || product.show_attributes_quantity){
        $('> *', $box).hide();
        return '';
    }

    var listingName = state['widgets'][widgetId]['listingName'];
    var itemElements = state['productListings'][listingName]['itemElements'];
    var hasAttributes = +state['products'][productId]['product_has_attributes'];
    var isBundle = +state['products'][productId]['isBundle'];
    if (!itemElements.attributes && hasAttributes || isBundle) {
        $('> *', $box).remove();
        return '';
    }

    tl.subscribe(['widgets', widgetId, 'products', productId, 'canAddToCart'], function(){
        var state = tl.store.getState();
        if (
            !isElementExist(['widgets', widgetId, 'products', productId, 'canAddToCart'], state)
            && !(+state['products'][productId]['is_virtual'])
        ) {
            $box.hide();
        } else {
            $box.show();
        }
    });

    $input.quantity();

    $input.on('change', changeQty);
    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty'], state)) {
        $input.val(state['widgets'][widgetId]['products'][productId]['qty'])
    } else {
        changeQty();
    }

    function changeQty(){
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_QTY',
            value: {
                widgetId: widgetId,
                productId: productId,
                qty: $input.val(),
            },
            file: 'boxes/ProductListing/applyItemQtyInput'
        })
    }

    var $qty_ = [];
    $qty_[0] = $("input[name$='_[0]']", $box);
    if ($qty_[0].length && $qty_[0].val().length) {
        $qty_[0].quantity();
        $qty_[0].on('change', function() { changePackQty(0); });
    }
    $qty_[1] = $("input[name$='_[1]']", $box);
    if ($qty_[1].length && $qty_[1].val().length) {
        $qty_[1].quantity();
        $qty_[1].on('change', function() { changePackQty(1); });
    }
    $qty_[2] = $("input[name$='_[2]']", $box);
    if ($qty_[2].length && $qty_[2].val().length) {
        $qty_[2].quantity();
        $qty_[2].on('change', function() { changePackQty(2); });
    }
    function changePackQty(index){
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_PACK_QTY',
            value: {
                widgetId: widgetId,
                productId: productId,
                qty_: (index + 1),
                qty: $qty_[index].val(),
            },
            file: 'boxes/ProductListing/applyItemQtyInput'
        })
    }
}
/* End file "boxes/ProductListing/applyItemQtyInput" */


/* Start file "boxes/ProductListing/applyItemCompare" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemCompare = function($item, widgetId) {
    var productId = $item.data('id');
    var $checkbox = $('.compare input', $item);
    var $viewButton = $('.compare .view', $item);
    var params = {};
    params.compare = [];

    var currentCategoryId = 0
    if (isElementExist(['compare', 'currentCategory', 'id'], entryData)) {
        currentCategoryId = entryData.compare.currentCategory.id;
    } else {
        var url = new URL(window.location.href);
        currentCategoryId = url.searchParams.get("currentCategoryId");
        if (!currentCategoryId) {
            $('.compare', $item).hide();
            return;
        }
    }

    tl.subscribe(['productListings', 'compare', 'byCategory'], function(){
        var state = tl.store.getState();
        params.compare = state['productListings']['compare']['byCategory'];

        if (
            isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state) &&
            state['productListings']['compare']['byCategory'][currentCategoryId].indexOf(productId) !== -1
        ) {
            $checkbox.prop('checked', true);

            if (state['productListings']['compare']['byCategory'][currentCategoryId].length > 1) {
                $viewButton.show()
            } else {
                $viewButton.hide()
            }
        } else {
            $checkbox.removeAttr('checked');
            $viewButton.hide()
        }
    });

    /*$viewButton.popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCompare'><div class='pop-up-close'></div><div class='popup-heading compare-head'>" + entryData.tr.BOX_HEADING_COMPARE_LIST + "</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
        data: params
    })*/

    $viewButton.on('click', function (e) {
        var state = tl.store.getState();
        if (isElementExist(['productListings', 'compare', 'byCategory', currentCategoryId], state)) {
            params.compare = state['productListings']['compare']['byCategory'][currentCategoryId];
            params.currentCategoryId = currentCategoryId
        }
        e.preventDefault();
        window.location = $viewButton.attr('href') + '?' + $.param( params );
    })

    $checkbox.on('change', function(){
        var qty = 0;
        if ($checkbox.prop('checked')) {

            tl.store.dispatch({
                type: 'ADD_TO_COMPARE',
                value: {
                    productId: productId,
                    categoryId: currentCategoryId || 0,
                },
                file: 'boxes/ProductListing/applyItemCompare'
            });
            updateCompare('add-to-compare', productId)

        } else {
            tl.store.dispatch({
                type: 'REMOVE_FROM_COMPARE',
                value: {
                    productId: productId,
                    categoryId: currentCategoryId,
                },
                file: 'boxes/ProductListing/applyItemCompare'
            });
            updateCompare('remove-from-compare', productId)
        }

        var state = tl.store.getState();
        localStorage.setItem('compareByCategory', JSON.stringify(state['productListings']['compare']['byCategory']))
    })

    function updateCompare(action, productId){
        $.get(entryData.mainUrl.replace(/\/$/, '') + '/catalog/' + action, {
            productId: productId,
            categoryId: currentCategoryId || 0
        }, function(response){
            if (!currentCategoryId) {
                tl.store.dispatch({
                    type: 'UPDATE_COMPARE',
                    value: response,
                    file: 'boxes/ProductListing/applyItemCompare'
                });
                localStorage.setItem('compareByCategory', JSON.stringify(response))
            }
        }, 'json')
    }
}

/* End file "boxes/ProductListing/applyItemCompare" */


/* Start file "boxes/ProductListing/applyItemBatchSelect" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBatchSelect = function($item, widgetId) {
    var productId = $item.data('id');
    var $checkbox = $('.batchSelect input', $item);
    var state = tl.store.getState();
    if (!isElementExist(['widgets', widgetId, 'batchSelectedWidget'], state)) {
        $('.batchSelect', $item).hide();
        return '';
    }

    var batchSelectedWidgetId = state['widgets'][widgetId]['batchSelectedWidget'];

    //checkUncheck();
    tl.subscribe(['productListings', 'batchSelectedProducts' + batchSelectedWidgetId, 'products'], checkUncheck);
    tl.subscribe(['widgets', widgetId, 'products', productId, 'attributes'], checkUncheck);

    $checkbox.on('change', function(){
        var state = tl.store.getState();
        var uprid = productId;
        var attributes = false;
        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            attributes = state['widgets'][widgetId]['products'][productId]['attributes'];
            uprid = helpers.getUprid(productId, attributes);
        }

        if ($checkbox.prop('checked')) {
            tl.store.dispatch({
                type: 'ADD_PRODUCT_TO_BATCH',
                value: {
                    productId: uprid,
                    attributes: attributes,
                    widgetId: batchSelectedWidgetId
                },
                file: 'boxes/ProductListing/applyItemBatchSelect'
            });
        } else {
            tl.store.dispatch({
                type: 'REMOVE_PRODUCT_FROM_BATCH',
                value: {
                    productId: uprid,
                    widgetId: batchSelectedWidgetId
                },
                file: 'boxes/ProductListing/applyItemBatchSelect'
            });
        }
    }).trigger('change');

    function checkUncheck(){
        var state = tl.store.getState();

        var uprid = productId;
        if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
            uprid = helpers.getUprid(productId, state['widgets'][widgetId]['products'][productId]['attributes'])
        }
        if (isElementExist(['productListings', 'batchSelectedProducts' + batchSelectedWidgetId, 'products', uprid], state)) {
            $checkbox.prop('checked', true);
        } else {
            $checkbox.removeAttr('checked');
        }
    }
}
/* End file "boxes/ProductListing/applyItemBatchSelect" */


/* Start file "boxes/ProductListing/applyItemBatchRemove" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBatchRemove = function($item, widgetId) {
    var productId = $item.data('id');
    $('.btn-batch-remove', $item).on('click', function(){
        tl.store.dispatch({
            type: 'REMOVE_PRODUCT_FROM_BATCH',
            value: {
                productId: productId,
                widgetId: widgetId,
            },
            file: 'boxes/ProductListing/applyItemBatchRemove'
        });
    });
}
/* End file "boxes/ProductListing/applyItemBatchRemove" */


/* Start file "boxes/ProductListing/applyItemProductGroup" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItemProductGroup = function($item, widgetId) {
//function reloadGroups($_listing, widgetId){
    var $rootBox = $('#box-'+widgetId);
    var $_listing = $rootBox.find('.product-listing');

    if ( $rootBox.data('group-init') ) return;

    $rootBox.on('click', '.js-list-prod', function(event){
        var $link = $(event.currentTarget);
        var prodUrl = $link.attr('href');
        var prodId = $link.data('productsId');
        var $itemBox = $link.parents('.item');
        var $productsListing = $link.parents('.products-listing');
        var listType = $productsListing.attr('data-listing-type');
        var listParam = $productsListing.attr('data-listing-param');
        var listCallback = $productsListing.attr('data-listing-callback');
        var boxId = $itemBox.parents('.box').attr('id') || '';
        var ajaxParam = {
            'products_id': prodId,
            'onlyFilter':'',
            'productListing': 1,
            'onlyProducts':1,
            'listType':listType,
            'listParam':listParam,
            'boxId': boxId
        };
        var listPreCallback = $productsListing.attr('data-listing-pre-callback');
        if ( listPreCallback && typeof window[listPreCallback] === 'function' ) {
            (window[listPreCallback])(event, ajaxParam);
        }
        $.get(window.productCellUrl?window.productCellUrl:window.location.href, ajaxParam, function(data){
            if ( listCallback && typeof window[listCallback] === 'function' ) {
                 if ( !(window[listCallback])(data) ) { return; }
            }
            var $newItem = $('<div>' + data.html + '</div>');
            var $items = $('.item', $newItem);
            tl.store.dispatch({
                type: 'ADD_PRODUCTS',
                value: {
                    products: data.entryData.products,
                },
                file: 'boxes/ProductListing'
            });
            $items.each(function(){
                ProductListing.applyItem($(this), widgetId);
            });
            if ( $itemBox.hasClass('slick-slide') ){
                $itemBox.attr('data-id',$items.attr('data-id'));
                $itemBox.data('id',$items.attr('data-id'));
                $itemBox.attr('data-name',$items.attr('data-name'));
                $itemBox.data('name',$items.attr('data-name'));
                $itemBox.children().replaceWith($items.children());
            }else{
                $itemBox.replaceWith($items);
            }
            //ProductListing.alignItems($rootBox.find($itemBox));
            ProductListing.alignItems($_listing);
            if($('.new_arrivals').length > 0){
                if($(window).width() > 800){
                    setTimeout(function(){
                        $('.new_arrivals .item:nth-child(2) .image').removeAttr('style');
                        var wrapHeight = $('.new_arrivals .item:nth-child(1)').innerHeight() + $('.new_arrivals .item:nth-child(3)').innerHeight();
                        var secondHeight = $('.new_arrivals .item:nth-child(2)').innerHeight();
                        var secondHeightImg = (wrapHeight - secondHeight + $('.new_arrivals .item:nth-child(2) .image').innerHeight());
                        $('.new_arrivals .item:nth-child(2) .image').css('min-height', secondHeightImg);
                    },1);
                }
            }
        }, 'json').fail(function(){
            window.location.href = prodUrl;
        });

        return false;
    });
    $rootBox.data('group-init', true);
}

/* End file "boxes/ProductListing/applyItemProductGroup" */


/* Start file "boxes/ProductListing/applyItem" */
if (!ProductListing) var ProductListing = {};
ProductListing.applyItem = function($item, widgetId) {
    var productId = $item.data('id');
    var state = tl.store.getState();

    ProductListing.applyItemData($item, widgetId);

    ProductListing.applyItemImage($item, widgetId);
    ProductListing.applyItemPrice($item, widgetId);
    ProductListing.applyItemStock($item, widgetId);
    ProductListing.applyItemQtyInput($item, widgetId);
    ProductListing.applyItemBuyButton($item, widgetId);
    ProductListing.applyItemAttributes($item, widgetId);
    ProductListing.applyItemCompare($item, widgetId);
    if (ProductListing.applyItemPersonalCatalog) {
        ProductListing.applyItemPersonalCatalog($item, widgetId);
    }
    ProductListing.applyItemBatchSelect($item, widgetId);
    ProductListing.applyItemBatchRemove($item, widgetId);
    ProductListing.applyItemProductGroup($item, widgetId);

    if (isElementExist(['products', productId, 'show_attributes_quantity'], state)) {
        ProductListing.updateAttributes(widgetId, productId, {
            products_id: productId,
            type: 'productListing'
        }, $item);
    }

    if (isElementExist(['extensions', 'productListing', 'applyItem'], tl)){
        for(var extension in tl.extensions.productListing.applyItem) {
            tl.extensions.productListing.applyItem[extension]($item, widgetId)
        }
    }

    if (pCarousel && pCarousel.addItem) {
        var product = state['products'][productId];
        var productImage = '<img\
                  src="' + product.image + '"\
                  alt="' + product.image_alt + '"\
                  title="' + product.image_title + '"'
            + (product.srcset ? 'srcset="' + product.srcset + '"' : '')
            + (product.sizes ? 'srcset="' + product.sizes + '"' : '') + '\>';

        var productPrice = '<div class="price">'
            + (product.price.special ? '<span class="old">' + product.price.old + '</span>' : '') +
            +(product.price.special ? '<span class="specials">' + product.price.special + '</span>' : '') +
            +(!product.price.special ? '<span class="current">' + product.price.current + '</span>' : '') +
            '</div>'

        pCarousel.addItem(productId, product.link, product.products_name, productImage, productPrice);
    }
}
/* End file "boxes/ProductListing/applyItem" */


/* Start file "boxes/ProductListing/carousel" */
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
/* End file "boxes/ProductListing/carousel" */


/* Start file "boxes/ProductListing/updateAttributes" */
if (!ProductListing) var ProductListing = {};
ProductListing.updateAttributes = function(widgetId, productId, data, $item){
    var $box = $('.attributes', $item);
    if ( widgetId && typeof data.boxId === 'undefined' ){
        data.boxId = widgetId;
    }
    $.ajax({
        url: getMainUrl() + '/catalog/product-attributes',
        data: data,
        dataType: 'json'
    })
        .done(function(data) {

            var price = {
                current: data.product_price,
                old: data.special_price ? data.product_price : '',
                special: data.special_price,
            }

            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PRODUCT_PRICE',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    price: price,
                },
                file: 'boxes/ProductListing/updateAttributes',
            })

            if (typeof data.stock_indicator !== "undefined") {
                tl.store.dispatch({
                    type: 'WIDGET_PRODUCT_STOCK_INDICATOR',
                    value: {
                        widgetId: widgetId,
                        productId: productId,
                        stock_indicator: data.stock_indicator,
                    },
                    file: 'boxes/ProductListing/updateAttributes'
                });
            }

            tl.store.dispatch({
                type: 'WIDGET_PRODUCT_IN_CART',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    productInCart: data.product_in_cart,
                },
                file: 'boxes/ProductListing/updateAttributes'
            });


            $box.removeClass('loader');
            $box.html(data.product_attributes);
            ProductListing.applyItemAttributes($item, widgetId)

            ProductListing.alignItems($('#box-' + widgetId + ' .products-listing'))
        })
        .fail(function() {
            /*tl.store.dispatch({
                type: 'WIDGET_CHANGE_PRODUCT_ATTRIBUTE',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    attributes: attributes,
                },
                file: 'boxes/ProductListing/updateAttributes',
            })*/
        });
}
/* End file "boxes/ProductListing/updateAttributes" */


/* Start file "boxes/ProductListing/addProductToCart" */
if (!ProductListing) var ProductListing = {};
ProductListing.addProductToCart = function(widgetId, productId){
    var state = tl.store.getState();

    var qty;
    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty'], state)) {
        qty = state.widgets[widgetId]['products'][productId]['qty']
    } else {
        qty = 1;
    }

    tl.store.dispatch({
        type: 'WIDGET_ADDING_PRODUCT_TO_LIST',
        value: {
            widgetId: widgetId,
            productId: productId,
            list: 'cart',
        },
        file: 'boxes/ProductListing'
    });

    var postData = [];
    postData.push({name: 'action', value: 'buy_now'});
    postData.push({name: 'qty', value: qty});
    postData.push({name: 'products_id', value: productId});

    if (isElementExist(['widgets', widgetId, 'products', productId, 'qty_'], state)) {
        var qty_ = state.widgets[widgetId]['products'][productId]['qty_'];
        for (var index in qty_){
            postData.push({name: 'qty_[' + (index - 1) + ']', value: qty_[index]});
        }
    }
    if (isElementExist(['widgets', widgetId, 'products', productId, 'attributes'], state)) {
        var attributes = state.widgets[widgetId]['products'][productId]['attributes'];
        for (var attrKey in attributes){
            postData.push({name: attrKey, value: attributes[attrKey]});
        }
    }
    if (isElementExist(['widgets', widgetId, 'products', productId, 'mixAttributes'], state)) {
        var attributes = state.widgets[widgetId]['products'][productId]['mixAttributes'];
        for (var attributeId in attributes){
            for (var optionId in attributes[attributeId]) {
                if (attributes[attributeId][optionId]) {
                    postData.push({name: 'mix_attr[' + productId + '][]['+attributeId+']', value: optionId});
                    postData.push({name: 'mix[]', value: productId});
                    postData.push({name: 'mix_qty[' + productId + '][]', value: attributes[attributeId][optionId]});
                }
            }
        }
    }

    postData.push({name: '_csrf', value: $('meta[name="csrf-token"]').attr('content')});
    postData.push({name: 'json', value: 1});

    $.ajax({
        url: getMainUrl() + '/?action=add_product',
        data: postData,
        method: 'post',
        dataType: 'json'
    })
        .done(function(data) {
			var state = tl.store.getState();
            if (data.error) {                
                window.location.href = state['products'][productId]['link'];
            }

            var products = {};

            data.products.forEach(function(prod){
                products[prod.stock_products_id] = {
                    id: prod.id,
                    qty: prod.quantity
                }
            });

            tl.store.dispatch({
                type: 'WIDGET_NO_ADDING_PRODUCT_TO_LIST',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    list: 'cart',
                },
                file: 'boxes/ProductListing/addProductToCart'
            });
            tl.store.dispatch({
                type: 'UPDATE_PRODUCTS_IN_LIST',
                value: {
                    listingName: 'cart',
                    products: products,
                },
                file: 'boxes/ProductListing/addProductToCart'
            });

            tl.store.dispatch({
                type: 'WIDGET_PRODUCT_IN_CART',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    productInCart: true,
                },
                file: 'boxes/ProductListing/addProductToCart'
            });

            if(isElementExist(['widgets', widgetId, 'showPopup'], state) && state['widgets'][widgetId]['showPopup']){
				window.location.href = getMainUrl() + '/shopping-cart';
			}else{
				$('<a href="' + getMainUrl() + '/shopping-cart?popup=1" data-class="cart-popup"></a>').popUp().trigger('click');
			}
        })
        .fail(function() {
            tl.store.dispatch({
                type: 'WIDGET_NO_ADDING_PRODUCT_TO_LIST',
                value: {
                    widgetId: widgetId,
                    productId: productId,
                    list: 'cart',
                },
                file: 'boxes/ProductListing/addProductToCart'
            });
        });

}
/* End file "boxes/ProductListing/addProductToCart" */


/* Start file "boxes/ProductListing/alignItems" */
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

/* End file "boxes/ProductListing/alignItems" */


/* Start file "boxes/ProductListing/productListingCols" */
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

/* End file "boxes/ProductListing/productListingCols" */


/* Start file "boxes/ProductListing/fbl" */
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
/* End file "boxes/ProductListing/fbl" */


/* Start file "reducers/products" */
tl.reducers.products = function(state, actions){
    if (!state) state = entryData.products;
    if (!state) state = [];

    var newState ='';

    switch (actions.type) {
        case 'CHANGE_PRODUCT':
        case 'ADD_PRODUCT':
            newState = JSON.parse(JSON.stringify(state));
            newState[actions.value.id] = actions.value.product;
            return newState;
        case 'ADD_PRODUCTS':
            newState = JSON.parse(JSON.stringify(state));
            for (var id in actions.value.products) {
                newState[id] = actions.value.products[id];
            }
            return newState;
        case 'CHANGE_PRODUCT_IMAGE':
            newState = JSON.parse(JSON.stringify(state));
            newState[actions.value.id].defaultImage = actions.value.defaultImage;
            return newState;
        case 'CHANGE_PRODUCT_IMAGES':
            newState = JSON.parse(JSON.stringify(state));
            if (actions.value.defaultImage) {
                newState[actions.value.id].defaultImage = actions.value.defaultImage;
            }
            if (actions.value.images) {
                newState[actions.value.id].images = actions.value.images;
            }
            return newState;
        default:
            return state
    }
}
/* End file "reducers/products" */


/* Start file "reducers/productListings" */
tl.reducers.productListings = function(state, actions){
    if (!state) state = entryData.productListings;
    if (!state) state = [];

    var listingName, productId, qty, products, href, paramName, paramValue;

    if (actions && actions.value) {
        if (actions.value.listingName) listingName = actions.value.listingName;
        if (actions.value.productId) productId = actions.value.productId;
        if (actions.value.qty || actions.value.qty === 0) qty = actions.value.qty;
        if (actions.value.products) products = actions.value.products;
        if (actions.value.href) href = actions.value.href;
        if (actions.value.paramName) paramName = actions.value.paramName;
        if (actions.value.paramValue) paramValue = actions.value.paramValue;
        if (actions.value.widgetId) widgetId = actions.value.widgetId;
    }

    var newState ='';
    switch (actions.type) {
        case 'CHANGE_LISTING':
        case 'ADD_PRODUCT_IN_LIST':
        case 'UPDATE_PRODUCTS_IN_LIST':
        case 'REMOVE_PRODUCT_FROM_LIST':

            if (!listingName) {
                console.error(actions.type + ": lack of listingName");
                return state
            }

            newState = JSON.parse(JSON.stringify(state));
    }

    switch (actions.type) {
        case 'ADD_PRODUCT_IN_LIST':
            if (!productId) {
                console.error("ADD_PRODUCT_TO_LIST: lack of productId");
                return state
            }

            if (!qty) {
                if (isElementExist([listingName, 'products', productId, 'qty'], newState)){
                    qty = newState[listingName]['products'][productId]['qty']
                } else {
                    qty = 1;
                }
            }

            setElementInObject([listingName, 'products', productId, 'qty'], newState, qty);

            if (actions.value.attributes) {
                setElementInObject([listingName, 'products', productId, 'attributes'], newState, actions.value.attributes);
            }

            return newState;

        case 'REMOVE_PRODUCT_FROM_LIST':
            if (!productId) {
                console.error("REMOVE_PRODUCT_FROM_LIST: lack of productId");
                return state
            }

            if (isElementExist([listingName, 'products', productId], newState)){
                delete newState[listingName]['products'][productId]
            }

            return newState;

        case 'UPDATE_PRODUCTS_IN_LIST':

            products = JSON.parse(JSON.stringify(products));
            setElementInObject([listingName, 'products'], newState, products);

            return newState;

        case 'PRODUCTS_LISTING_HREF':
            newState = JSON.parse(JSON.stringify(state));

            newState.href = href;

            return newState;

        case 'PRODUCTS_LISTING_HREF_GET_PARAM':
            newState = JSON.parse(JSON.stringify(state));

            newState.href = setGetParam(newState.href, paramName, paramValue);

            return newState;

        case 'ADD_PRODUCT_TO_COMPARE':
            newState = JSON.parse(JSON.stringify(state));

            if (!newState.compare) newState.compare = {};
            if (!newState.compare.products) newState.compare.products = [];
            if (newState.compare.products.indexOf(productId) === -1) {
                newState.compare.products.push(productId);
                //newState.compare.products = newState.compare.products.slice(-4);
            }

            return newState;

        case 'REMOVE_PRODUCT_FROM_COMPARE':
            newState = JSON.parse(JSON.stringify(state));

            newState.compare.products.splice(newState.compare.products.indexOf(productId), 1);

            return newState;

        case 'ADD_PRODUCT_TO_BATCH':
            newState = JSON.parse(JSON.stringify(state));

            if (!newState['batchSelectedProducts' + widgetId])
                newState['batchSelectedProducts' + widgetId] = {};
            if (!newState['batchSelectedProducts' + widgetId]['products'])
                newState['batchSelectedProducts' + widgetId].products = {};

            var sortOrder = 0;
            for(var _dummy in newState['batchSelectedProducts' + widgetId]['products']) sortOrder++;
            newState['batchSelectedProducts' + widgetId]['products'][productId] = {'products_id': productId, attributes: (actions.value.attributes || false), sortOrder:sortOrder+1};

            return newState;

        case 'REMOVE_PRODUCT_FROM_BATCH':
            newState = JSON.parse(JSON.stringify(state));

            if (isElementExist(['batchSelectedProducts' + widgetId, 'products', productId], newState)) {
                delete newState['batchSelectedProducts' + widgetId]['products'][productId];
            }

            return newState;

        case 'PRODUCTS_LISTING_ITEM_ELEMENTS':
            newState = JSON.parse(JSON.stringify(state));

            setElementInObject([listingName, 'itemElements'], newState, actions.value.itemElements);

            return newState;

        case 'UPDATE_COMPARE':
            newState = JSON.parse(JSON.stringify(state));

            if (!newState.compare) newState.compare = {};
            newState.compare.byCategory = actions.value;

            return newState;

        case 'ADD_TO_COMPARE':
            newState = JSON.parse(JSON.stringify(state));

            if (!newState.compare)
                newState.compare = {};
            if (!newState.compare.byCategory)
                newState.compare.byCategory = {};
            if (!newState.compare.byCategory[actions.value.categoryId])
                newState.compare.byCategory[actions.value.categoryId] = [];
            if (!newState.compare.byCategory[actions.value.categoryId].includes(+actions.value.productId))
                newState.compare.byCategory[actions.value.categoryId].push(+actions.value.productId)

            return newState;

        case 'REMOVE_FROM_COMPARE':
            newState = JSON.parse(JSON.stringify(state));

            if (!actions.value.productId) {
                console.error(actions.type + ": need productId");
                return state
            }
            if (!actions.value.categoryId) {
                console.error(actions.type + ": need categoryId");
                return state
            }

            if (!newState.compare) return state;
            if (!newState.compare.byCategory) return state;
            if (!newState.compare.byCategory[actions.value.categoryId]) return state;
            if (!newState.compare.byCategory[actions.value.categoryId].includes(actions.value.productId)) return state;

            var index = newState.compare.byCategory[actions.value.categoryId].indexOf(actions.value.productId);
            if (index > -1) {
                newState.compare.byCategory[actions.value.categoryId].splice(index, 1);
            }

            return newState;

        default:
            return state
    }
}
/* End file "reducers/productListings" */


/* Start file "reducers/widgets" */
tl.reducers.widgets = function(state, actions){
    if (!state) state = entryData.widgets;
    if (!state) state = [];

    var widgetId, productId, qty, qty_, price, attributes, canAddToCart, productInCart, stock_indicator, list, attributeId, optionId, listingType;

    if (actions && actions.value) {
        if (actions.value.widgetId) widgetId = actions.value.widgetId;
        if (actions.value.productId) productId = actions.value.productId;
        if (actions.value.qty) qty = actions.value.qty;
        if (actions.value.qty_) qty_ = actions.value.qty_;
        if (actions.value.price) price = actions.value.price;
        if (actions.value.attributes) attributes = JSON.parse(JSON.stringify(actions.value.attributes));
        if (actions.value.canAddToCart) canAddToCart = actions.value.canAddToCart;
        if (actions.value.productInCart) productInCart = actions.value.productInCart;
        if (actions.value.stock_indicator) stock_indicator = actions.value.stock_indicator;
        if (actions.value.list) list = actions.value.list;
        if (actions.value.attributeId) attributeId = actions.value.attributeId;
        if (actions.value.optionId) optionId = actions.value.optionId;
        if (actions.value.listingType) listingType = actions.value.listingType;
    }

    var newState ='';
    switch (actions.type) {
        case 'WIDGET_CHANGE_PRODUCT_PRICE':
        case 'WIDGET_CHANGE_PRODUCT_ATTRIBUTE':
        case 'WIDGET_CHANGE_PRODUCT_MIX_ATTRIBUTE':
        case 'WIDGET_CLEAR_PRODUCT_MIX_ATTRIBUTE':
        case 'WIDGET_CHANGE_PRODUCT_QTY':
        case 'WIDGET_CHANGE_PRODUCT_PACK_QTY':
        case 'WIDGET_ADDING_PRODUCT_TO_LIST':
        case 'WIDGET_NO_ADDING_PRODUCT_TO_LIST':
        case 'WIDGET_PRODUCT_CAN_ADD_TO_CART':
        case 'WIDGET_PRODUCT_IN_CART':
        case 'WIDGET_PRODUCT_STOCK_INDICATOR':

            if (!widgetId || !productId) {
                console.error(actions.type + ": lack of parameters");
                return state
            }

            newState = JSON.parse(JSON.stringify(state));
    }

    switch (actions.type) {
        case 'WIDGET_CHANGE_LISTING_TYPE':
        case 'WIDGET_CHANGE_SETTINGS':
        case 'WIDGET_CHANGE_LISTING_SORTING':
        case 'WIDGET_CHANGE_PRODUCTS_ON_PAGE':
        case 'WIDGET_CHANGE_NUMBER_OF_PRODUCTS':
        case 'WIDGET_CHANGE_PAGE_COUNT':
        case 'WIDGET_PAGE_COUNT_UPDATE_PAGE':
        case 'WIDGET_CLEAR_PRODUCTS':

            if (!widgetId) {
                console.error(actions.type + ": lack of widgetId");
                return state
            }

            newState = JSON.parse(JSON.stringify(state));
    }

    switch (actions.type) {
        case 'WIDGET_CHANGE_PRODUCT_PRICE':

            setElementInObject([widgetId, 'products', productId, 'price'], newState, price);
            return newState;

        case 'WIDGET_CHANGE_PRODUCT_ATTRIBUTE':

            setElementInObject([widgetId, 'products', productId, 'attributes'], newState, attributes);
            return newState;

        case 'WIDGET_CHANGE_PRODUCT_MIX_ATTRIBUTE':

            setElementInObject([widgetId, 'products', productId, 'mixAttributes', attributeId, optionId], newState, qty);
            return newState;

        case 'WIDGET_CLEAR_PRODUCT_MIX_ATTRIBUTE':

            setElementInObject([widgetId, 'products', productId, 'mixAttributes'], newState, {});
            return newState;

        case 'WIDGET_CHANGE_PRODUCT_QTY':

            setElementInObject([widgetId, 'products', productId, 'qty'], newState, qty);
            return newState;

        case 'WIDGET_CHANGE_PRODUCT_PACK_QTY':

            setElementInObject([widgetId, 'products', productId, 'qty_', qty_], newState, qty);
            return newState;

        case 'WIDGET_ADDING_PRODUCT_TO_LIST':

            list = list.charAt(0).toUpperCase() + list.slice(1);
            setElementInObject([widgetId, 'products', productId, 'addingTo' + list], newState, true);
            return newState;

        case 'WIDGET_NO_ADDING_PRODUCT_TO_LIST':

            list = list.charAt(0).toUpperCase() + list.slice(1);
            setElementInObject([widgetId, 'products', productId, 'addingTo' + list], newState, false);
            return newState;

        case 'WIDGET_PRODUCT_CAN_ADD_TO_CART':

            setElementInObject([widgetId, 'products', productId, 'canAddToCart'], newState, canAddToCart);
            return newState;

        case 'WIDGET_PRODUCT_IN_CART':

            setElementInObject([widgetId, 'products', productId, 'productInCart'], newState, productInCart);
            return newState;

        case 'WIDGET_PRODUCT_STOCK_INDICATOR':

            setElementInObject([widgetId, 'products', productId, 'stock_indicator'], newState, stock_indicator);
            return newState;

        case 'WIDGET_CHANGE_LISTING_TYPE':

            setElementInObject([widgetId, 'listingType'], newState, listingType);
            return newState;

        case 'WIDGET_CHANGE_LISTING_SORTING':

            setElementInObject([widgetId, 'listingSorting'], newState, actions.value.listingSorting);
            return newState;

        case 'WIDGET_CHANGE_PRODUCTS_ON_PAGE':

            setElementInObject([widgetId, 'productsOnPage'], newState, actions.value.productsOnPage);
            return newState;

        case 'WIDGET_CHANGE_NUMBER_OF_PRODUCTS':

            setElementInObject([widgetId, 'numberOfProducts'], newState, actions.value.numberOfProducts);
            return newState;

        case 'WIDGET_CHANGE_PAGE_COUNT':

            setElementInObject([widgetId, 'pageCount'], newState, actions.value.pageCount);
            return newState;

        case 'WIDGET_PAGE_COUNT_UPDATE_PAGE':

            setElementInObject([widgetId, 'pageCountUpdatePage'], newState, actions.value.pageCountUpdatePage);
            return newState;

        case 'WIDGET_CLEAR_PRODUCTS':

            setElementInObject([widgetId, 'products'], newState, {});
            return newState;

        case 'WIDGET_CHANGE_SETTINGS':

            setElementInObject([widgetId, actions.value.settingName], newState, actions.value.settingValue);
            return newState;

        default:
            return state
    }
}
/* End file "reducers/widgets" */


/* Start file "modules/helpers/getUprid" */
if (!helpers) var helpers = {};
helpers.getUprid = function($productId, attributes){
    if (!$productId) return false;

    var uprid = $productId;

    if(typeof attributes === "object") {
        var newAttributes = {};
        var attributeIds = [];


        for (var attributeId in attributes){
            if (!attributes.hasOwnProperty(attributeId)) continue;

            var optionId = attributes[attributeId];

            if (attributeId.search('[a-z]') !== -1){
                attributeId = attributeId.match('[0-9]+')[0];
            }
            attributeIds.push(+attributeId)

            newAttributes[attributeId] = optionId;
        }

        attributeIds = attributeIds.sort(function (a, b) { return a - b});

        var attrId = 0;
        var key = 0;
        for (key in attributeIds) {
            attrId = attributeIds[key];

            uprid = uprid + '{' + attrId + '}' + newAttributes[attrId]
        }
    }

    return uprid;
}
/* End file "modules/helpers/getUprid" */



/* Start widget "catalog\ListingLook" */

tl(function(){
    $('.w-catalog-listing-look').each(function(){

        var state = tl.store.getState();
        if (!isElementExist(['productListings', 'mainListing'], state)) return '';

        var $listingLook = $(this);
        var widgetId = $listingLook.attr('id').substring(4);
        var $listingLookLinks = $('a', $listingLook);

        $listingLookLinks.on('click', function(e){
            $listingLookLinks.removeClass('active');
            $(this).addClass('active');

            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return true
            }

            e.preventDefault();
            var listingId = state.productListings.mainListing;
            var gl = $(this).data('gl');

            var listingType = state['widgets'][listingId][$(this).data('type')]

            tl.store.dispatch({
                type: 'WIDGET_CLEAR_PRODUCTS',
                value: {
                    widgetId: listingId,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'gl',
                    paramValue: gl,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_LISTING_TYPE',
                value: {
                    widgetId: listingId,
                    listingType: listingType,
                },
                file: 'boxes/catalog/ListingLook'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/ListingLook'
            });
        })
    })
})

/* End widget "catalog\ListingLook" */



/* Start widget "catalog\CompareButton" */

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
    if(params.compare)
        tl.subscribe(['productListings', 'compare'], function(){
            var state = tl.store.getState();

            params.compare = state['productListings']['compare']['byCategory'];
            if (params.compare.length > 1) {
                $compareButton.show();
            } else {
                $compareButton.hide();
            }
        });
})

/* End widget "catalog\CompareButton" */



/* Start widget "catalog\Sorting" */

tl(function(){
    $('.w-catalog-sorting').each(function(){

        var $listingSorting = $(this);
        var widgetId = $listingSorting.attr('id').substring(4);
        var $listingSortingSelect = $('select', $listingSorting);

        $listingSortingSelect.on('change', function(e){
            e.preventDefault();
            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return false
            }

            var listingId = state.productListings.mainListing;
            var sort = $(this).val();

            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/Sorting'
            });
            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'sort',
                    paramValue: sort,
                },
                file: 'boxes/catalog/Sorting'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_LISTING_SORTING',
                value: {
                    widgetId: listingId,
                    listingSorting: sort,
                },
                file: 'boxes/catalog/Sorting'
            });
        })
    })
})

/* End widget "catalog\Sorting" */



/* Start widget "catalog\ItemsOnPage" */

tl(function(){
    $('.w-catalog-items-on-page').each(function(){

        var $listingCount = $(this);
        var widgetId = $listingCount.attr('id').substring(4);
        var $listingCountSelect = $('select', $listingCount);

        $listingCountSelect.on('change', function(e){
            e.preventDefault();
            var state = tl.store.getState();
            if (!state.productListings || !state.productListings.mainListing){
                return false
            }

            var listingId = state.productListings.mainListing;
            var maxItems = $(this).val();

            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'page',
                    paramValue: 1,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PAGE_COUNT',
                value: {
                    widgetId: listingId,
                    pageCount: 1,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });

            tl.store.dispatch({
                type: 'PRODUCTS_LISTING_HREF_GET_PARAM',
                value: {
                    paramName: 'max_items',
                    paramValue: maxItems,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
            tl.store.dispatch({
                type: 'WIDGET_CHANGE_PRODUCTS_ON_PAGE',
                value: {
                    widgetId: listingId,
                    productsOnPage: maxItems,
                },
                file: 'boxes/catalog/ItemsOnPage'
            });
        })
    })
})

/* End widget "catalog\ItemsOnPage" */



/* Start widget "catalog\B2bAddButton" */

tl(function(){
    $('.w-catalog-b2b-add-button').each(function(){

        var $b2bBox = $(this);
        var widgetId = $b2bBox.attr('id').substring(4);
        var $addButton = $('.add-b2b-products', $b2bBox);

        var state = tl.store.getState();
        if (!state.productListings || !state.productListings.mainListing){
            return true
        }

        var listingId = state.productListings.mainListing;

        if (state['widgets'][listingId]['listingType'] == state['widgets'][listingId]['listingTypeB2b']){
            $b2bBox.show()
        } else {
            $b2bBox.hide()
        }

        tl.subscribe(['widgets', listingId, 'listingType'], function () {
            var state = tl.store.getState();
            if (state['widgets'][listingId]['listingType'] == state['widgets'][listingId]['listingTypeB2b']){
                $b2bBox.show()
            } else {
                $b2bBox.hide()
            }
        });


        $addButton.on('click', function(){
            $(this).addClass('loader')
            var state = tl.store.getState();
            var postData = [];
            postData.push({name: '_csrf', value: $('meta[name="csrf-token"]').attr('content')});
            postData.push({name: 'json', value: 1});
            for (var productId in state['widgets'][listingId]['products']) {

                postData.push({name: 'qty[]', value: state.widgets[listingId]['products'][productId]['qty']});
                postData.push({name: 'products_id[]', value: productId});

                if (isElementExist(['widgets', listingId, 'products', productId, 'qty_'], state)) {
                    var qty_ = state.widgets[listingId]['products'][productId]['qty_'];
                    for (var index in qty_){
                        postData.push({name: 'qty_[' + productId + '][' + (index - 1) + ']', value: qty_[index]});
                    }
                }
                if (isElementExist(['widgets', listingId, 'products', productId, 'attributes'], state)) {
                    var attributes = state.widgets[listingId]['products'][productId]['attributes'];
                    for (var attrKey in attributes){
                        postData.push({name: attrKey, value: attributes[attrKey]});
                    }
                }
                if (isElementExist(['widgets', listingId, 'products', productId, 'mixAttributes'], state)) {
                    var attributes = state.widgets[listingId]['products'][productId]['mixAttributes'];
                    for (var attributeId in attributes){
                        for (var optionId in attributes[attributeId]) {
                            if (attributes[attributeId][optionId]) {
                                postData.push({name: 'mix_attr[' + productId + '][]['+attributeId+']', value: optionId});
                                postData.push({name: 'mix[]', value: productId});
                                postData.push({name: 'mix_qty[' + productId + '][]', value: attributes[attributeId][optionId]});
                            }
                        }
                    }
                }

            }


            $.ajax({
                url: getMainUrl() + '?action=add_all',
                data: postData,
                method: 'post',
                //dataType: 'json'
            })
                .done(function(data) {

                    window.location.href = getMainUrl() + '/shopping-cart';
                })
        })


    })
})

/* End widget "catalog\B2bAddButton" */



/* Start widget "catalog\CountsItems" */

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

/* End widget "catalog\CountsItems" */



/* Start widget "catalog\Paging" */

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

            $catalogPaging.html($paging);

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

/* End widget "catalog\Paging" */



/* Start widget "Compare" */

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

/* End widget "Compare" */



/* Start widget "Menu" */

tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const $menuStyle = $('.menu-style', $menu);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        const settings = state.widgets[widgetId].settings

        tl.store.dispatch({
            type: 'WIDGET_CHANGE_SETTINGS',
            value: {
                widgetId: widgetId,
                settingName: 'params',
                settingValue: settings[0],
            },
            file: 'boxes/Menu 1'
        });

        layoutChange('', document.body.classList.value.split(' ').filter(cl => cl.match(/^[0-9w]+$/)))
        $(window).on('layoutChange', layoutChange)


        function layoutChange(e, d){
            const current = d.current || d;
            for (let setting in settings.visibility) {
                const state = tl.store.getState();
                const params = state.widgets[widgetId].params ? JSON.parse(JSON.stringify(state.widgets[widgetId].params)) : {};
                let value = settings[0] ? settings[0][setting] : false;

                for (let limits in settings.visibility[setting]) {
                    if (current.includes(limits) && settings.visibility[setting][limits]) {
                        value = settings.visibility[setting][limits];
                    }
                }

                if (value != params[setting]) {
                    params[setting] = value ?? false;

                    if (params[setting]) {
                        $menuStyle.attr('data-' + setting, params[setting])
                    } else {
                        $menuStyle.removeAttr('data-' + setting)
                    }
                    tl.store.dispatch({
                        type: 'WIDGET_CHANGE_SETTINGS',
                        value: {
                            widgetId: widgetId,
                            settingName: 'params',
                            settingValue: params,
                        },
                        file: 'boxes/Menu 2'
                    });
                }

            }
        }
    })
})

/* End widget "Menu" */

tl.combineReducers = Redux.combineReducers(tl.reducers);

tl.store = Redux.createStore(tl.combineReducers, window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__())

tl.backState = tl.store.getState();

tl.allSubscribers = [];

tl.subscribe = function(key, callBack) {
    tl.allSubscribers.push({key: key, callBack: callBack})
}

tl.store.subscribe(function(){
    var state = tl.store.getState();

    tl.allSubscribers.forEach(function(subscribe){
        var key = subscribe.key;
        var callBack = subscribe.callBack;

        if (typeof key === 'string'){
            if (JSON.stringify(state[key]) !== JSON.stringify(tl.backState[key])) {
                callBack()
            }
        }
        if (Array.isArray(key)){
            if (isDifferentElements(key, state, tl.backState)) {
                callBack()
            }
        }
    })
    tl.backState = state;
});

function isDifferentElements(path, obj1, obj2){
    if (path.length > 1) {
        if (typeof obj1[path[0]] === 'object' && typeof obj2[path[0]] === 'object') {

            return isDifferentElements(path.slice(1), obj1[path[0]], obj2[path[0]])

        } else if(
            typeof obj1[path[0]] === 'object' && isElementExist(path, obj1) ||
            typeof obj2[path[0]] === 'object' && isElementExist(path, obj2)
        ){

            return true
        }
    } else if (JSON.stringify(obj1[path[0]]) !== JSON.stringify(obj2[path[0]])) {
        return true
    }
    return false;
}

function isElementExist(path, obj){
    if (path.length > 1) {
        if (obj && typeof obj[path[0]] === 'object') {

            return isElementExist(path.slice(1), obj[path[0]])

        }
    } else if (obj[path[0]]) {
        return true
    }
    return false;
}

function setElementInObject(path, obj, value){
    var link = obj;
    path.slice(0, -1).forEach(function(element){
        if (!link[element]) link[element] = {};

        link = link[element];
    })
    link[path.pop()] = value
}

function setGetParam(href, paramName, paramValue){
    var res = '';
    var d = href.split("#")[0].split("?");
    var base = d[0];
    var query = d[1];
    if(d[1]) {
        var params = query.split("&");
        for(var i = 0; i < params.length; i++) {
            var keyval = params[i].split("=");
            if(keyval[0] != paramName) {
                res += encodeURI($('<div>' + decodeURI(params[i]) + '</div>').text()) + '&';
            }
        }
    }
    if (paramValue) {
        res += paramName + '=' + encodeURI($('<div>' + decodeURI(paramValue) + '</div>').text());
    }
    return base + (res ? '?' : '') + res;
}

function getMainUrl() {
    var mainUrl = '';
    if ((typeof(entryData) === 'object') && (entryData !== null) && (typeof(entryData.mainUrl) === 'string')) {
        mainUrl = ('' + entryData.mainUrl).replace(/(\/|\\)+$/g, '');
    }
    return mainUrl;
}

function createJsUrl(file) {
    if (typeof entryData.jsPathUrl !== 'string') {
        return file;
    }
    var newUrl = entryData.jsPathUrl.replace(/(\/|\\)+$/g, '');
    if (!file) {
        return newUrl;
    }
    newUrl = newUrl + '/' + file + (entryData.themeVersion ? '?' + entryData.themeVersion : '' );
    return newUrl;
}

tl(tlSize.init);tl_action(tl_js);

