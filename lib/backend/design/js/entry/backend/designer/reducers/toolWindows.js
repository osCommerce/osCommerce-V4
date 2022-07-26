import {SHOW_WINDOW, HIDE_WINDOW, TOGGLE_WINDOW} from '../actionTypes';

export default (state = getInitialState(), action) => {
    const newState = JSON.parse(JSON.stringify(state));

    switch (action.type) {
        case SHOW_WINDOW:
            newState[action.window] = true;
            break;
        case HIDE_WINDOW:
            newState[action.window] = false;
            break;
        case TOGGLE_WINDOW:
            newState[action.window] = !state[action.window];
            break;
    }
    return newState
}

function getInitialState(){
    return {
        addTheme: false
    };
}