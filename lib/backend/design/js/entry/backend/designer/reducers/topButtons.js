

import {HIDE_BUTTON, SHOW_BUTTON, TOGGLE_BUTTON} from "../actionTypes";

export default (state = getInitialState(), action) => {

    const newState = JSON.parse(JSON.stringify(state));

    switch (action.type) {
        case SHOW_BUTTON:
            newState[action.button] = true;
            break;
        case HIDE_BUTTON:
            newState[action.button] = false;
            break;
        case TOGGLE_BUTTON:
            newState[action.button] = !state[action.button];
            break;
    }
    return newState
}

function getInitialState(){
    return {
        addTheme: false
    };
}