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