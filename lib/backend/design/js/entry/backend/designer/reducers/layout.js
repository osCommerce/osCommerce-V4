import {TOGGLE_SIDEBAR, RESIZE_SIDEBAR, TOGGLE_MENU_TYPE, SET_PAGE_TITLE} from '../actionTypes';

export default (state = getInitialState(), action) => {
    const newState = JSON.parse(JSON.stringify(state));

    if (window.entryData && window.entryData.pageTitle) {
        newState.pageTitle = window.entryData.pageTitle;
    }

    switch (action.type) {
        case TOGGLE_SIDEBAR:
            newState.toggleSidebar = !state.toggleSidebar;
            break;
        case RESIZE_SIDEBAR:
            newState.sidebarWidth = action.sidebarWidth;
            break;
        case TOGGLE_MENU_TYPE:
            newState.menuType = action.menuType;
            break;
        case SET_PAGE_TITLE:
            newState.pageTitle = action.pageTitle;
            break;
    }

    localStorage.setItem('layout', JSON.stringify(newState));
    return newState;
}


function getInitialState(){
    let store = localStorage.getItem('layout');
    if (store) {
        return JSON.parse(store);
    }

    return {
        sidebarWidth: 250,
        toggleSidebar: true,
    };
}