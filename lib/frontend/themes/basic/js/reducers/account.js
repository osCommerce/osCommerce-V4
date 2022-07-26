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