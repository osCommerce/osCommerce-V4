import {SAVE_SETTING, SAVE_SUB_SETTING, CLOSE_TOOL, TO_POPUP, FROM_POPUP} from '../actionTypes';

export default (state = getInitialState(), action) => {

    let newState = JSON.parse(JSON.stringify(state));

    switch (action.type) {
        case SAVE_SETTING:
            newState[action.name] = action.value;
            break;
        case SAVE_SUB_SETTING:
            if (!newState[action.name]) newState[action.name] = {};
            newState[action.name][action.subName] = action.value;
            break;
        case CLOSE_TOOL:
        case TO_POPUP:
        case FROM_POPUP:
            newState.toolsList[action.areaName]
                = newState.toolsList[action.areaName].filter(item => item.name !== action.toolName);
            if (newState.activeTool[action.areaName] === action.toolName) {
                if (newState.toolsList[action.areaName][0]) {
                    newState.activeTool[action.areaName] = newState.toolsList[action.areaName][0].name;
                } else {
                    newState.activeTool[action.areaName] = '';
                }
            }
            if (action.type === TO_POPUP) {
                if (!newState.toolsList.toolWindows) newState.toolsList.toolWindows = [];
                newState.toolsList.toolWindows.push({name: action.toolName});
                newState.activeTool.toolWindows = action.toolName;
            }
            if (action.type === FROM_POPUP) {
                newState.toolsList.center.push({name: action.toolName});
                newState.activeTool.center = action.toolName;
            }
            break;
    }

    localStorage.setItem('designer', JSON.stringify(newState));
    return newState;

}

function getInitialState(){
    let store = localStorage.getItem('designer');
    if (store) {
        return JSON.parse(store);
    }

    return {
        leftAreaWidth: 200,
        rightAreaWidth: 200,
        topAreaHeight: 400,
        bottomAreaHeight: 200,
        themes: [],
        toolsList: {
            center: [{name: 'Themes'}/*, {name: 'Page'}*/],
            toolWindows: [{name: 'Themes'}]
        },
        activeTool: {
            center: 'Themes'
        }
    }
}