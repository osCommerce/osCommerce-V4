import {SAVE_SETTING, SAVE_SUB_SETTING, CLOSE_TOOL, TO_POPUP, FROM_POPUP} from '../actionTypes';
import fetchData from 'src/fetch-data';

export function saveSetting(name, value){
    return {
        type: SAVE_SETTING,
        name,
        value
    }
}

export function saveSubSetting(name, subName, value){
    return {
        type: SAVE_SUB_SETTING,
        name,
        subName,
        value
    }
}

export function closeTool(areaName, toolName){
    return {
        type: CLOSE_TOOL,
        areaName,
        toolName
    }
}

export function toPopUp(areaName, toolName){
    return {
        type: TO_POPUP,
        areaName,
        toolName
    }
}

export function fromPopUp(toolName){
    return {
        type: FROM_POPUP,
        areaName: 'toolWindows',
        toolName
    }
}

export function fetchThemes(){
    return async dispatch => {

        const themes = await fetchData('themes', 'get-list');
        dispatch(saveSetting('themes', themes))
    }
}