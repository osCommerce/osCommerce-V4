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


/* Start file "boxes/menu/lev_vis-click" */
tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);

        for (let level = 1; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_vis'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $('.level-' + level, $menu).parent('li.parent');
            const $a = $(' > a, > .no-link', $li);
            const $body = $('body');

            $a.off('click', clickItem);
            $li.removeClass('vis-show');
            $body.off('click', closeItem);

            const state = tl.store.getState();
            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_vis'], state) &&
                state.widgets[widgetId].params['lev'+level+'_vis'] == 'click') {

                $a.on('click', clickItem);
                $body.on('click', closeItem )
            }

            function closeItem(e){
                const $parents = $(e.target).parents('.vis-show');
                if ($parents.length == 0) {
                    $('.vis-show').removeClass('vis-show');
                    return
                }
                const $parent = $parents.get($parents.length - 1);
                $('.vis-show').each(function(){
                    if (!$.contains( $parent, this ) && this != $parent) {
                        $(this).removeClass('vis-show')
                    }
                })
            }
        }

        function clickItem(e){
            e.preventDefault()

            $(this).parent().toggleClass('vis-show');

        }

    })
})
/* End file "boxes/menu/lev_vis-click" */


/* Start file "boxes/menu/burger_icon" */
tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();
        const $icon = $('.burger-icon', $menu)

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        tl.subscribe(['widgets', widgetId, 'params', 'burger_icon'], apply);
        apply();

        function apply(){
            $icon.off('click');
            $menu.removeClass('bi-opened');

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'burger_icon'], state) &&
                state.widgets[widgetId].params.burger_icon
            ) {
                $icon.on('click', function(){
                    $menu.toggleClass('bi-opened')
                });
            }
        }
    })
})
/* End file "boxes/menu/burger_icon" */


/* Start file "boxes/menu/lev_display-width" */
tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const $liLevels = [0, 0,
            $('.menu-content > ul > li', $menu),
            $('.menu-content > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li > ul > li > ul > li', $menu),
        ];

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $liLevels[level];

            $('> ul', $li).css({'width': '', 'max-height': '', 'left': '', 'position': ''});
            $li.off('mouseenter', setStyles);

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'width'
            ) {
                $li.on('mouseenter', setStyles)
            }
        }

        function setStyles(){
            $('> ul', this).css({'width': '1px', 'height': '1px', 'left': '1px'});
            let left = - $(this).offset().left;
            let height = $(window).height() - ($(this).offset().top - $(window).scrollTop() + $(this).height());
            $('> ul', this).css({
                'width': $(window).width(),
                'height': height,
                'left': left,
                'position': 'absolute'
            })
        }
    })
})
/* End file "boxes/menu/lev_display-width" */



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



/* Start widget "Image" */

tl(createJsUrl('jquery.lazy.min.js'), function(){
    $('.w-image').each(function(){
        var widgetId = $(this).attr('id');
        if (!widgetId) {
            return ''
        }
        widgetId = widgetId.substring(4);
        if (!isElementExist(['widgets', widgetId, 'lazyLoad'], entryData)) {
            return ''
        }
        var e = $('img', this).lazy({
            bind: 'event'
        })
    })
});


/* End widget "Image" */

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

