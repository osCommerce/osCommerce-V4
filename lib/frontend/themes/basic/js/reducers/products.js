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