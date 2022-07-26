import {SHOW_BUTTON, HIDE_BUTTON, TOGGLE_BUTTON} from '../actionTypes';

export function showButton(button){
    return {
        type: SHOW_BUTTON,
        button
    }
}

export function hideButton(button){
    return {
        type: HIDE_BUTTON,
        button
    }
}

export function toggleButton(button){
    return {
        type: TOGGLE_BUTTON,
        button
    }
}