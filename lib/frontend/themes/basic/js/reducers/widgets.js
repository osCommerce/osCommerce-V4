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