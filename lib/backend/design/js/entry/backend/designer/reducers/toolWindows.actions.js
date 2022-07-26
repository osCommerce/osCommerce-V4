import {SHOW_WINDOW, HIDE_WINDOW, TOGGLE_WINDOW} from '../actionTypes';

export function showWindow(window){
    return {
        type: SHOW_WINDOW,
        window
    }
}

export function hideWindow(window){
    return {
        type: HIDE_WINDOW,
        window
    }
}

export function toggleWindow(window){
    return {
        type: TOGGLE_WINDOW,
        window
    }
}