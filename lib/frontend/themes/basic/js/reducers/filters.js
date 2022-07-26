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