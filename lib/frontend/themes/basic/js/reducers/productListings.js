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