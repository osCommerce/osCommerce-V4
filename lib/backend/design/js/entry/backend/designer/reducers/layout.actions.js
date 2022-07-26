import {TOGGLE_SIDEBAR, RESIZE_SIDEBAR, TOGGLE_MENU_TYPE, SET_PAGE_TITLE} from '../actionTypes';

export function toggleSidebar(){
    return {
        type: TOGGLE_SIDEBAR
    }
}

export function resizeSidebar(sidebarWidth){
    return {
        type: RESIZE_SIDEBAR,
        sidebarWidth
    }
}

export function toggleMenuType(menuType){
    return {
        type: TOGGLE_MENU_TYPE,
        menuType
    }
}

export function setPageTitle(pageTitle){
    return {
        type: SET_PAGE_TITLE,
        pageTitle
    }
}