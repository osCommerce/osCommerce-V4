import designer from './designer'
import {saveSetting, saveSubSetting, closeTool, fromPopUp, toPopUp} from './designer.actions'

describe('Testing designer reducer', () => {

    test('save designer setting', () => {
        localStorage.removeItem('designer');
        let newState = designer({}, saveSetting('leftAreaWidth', 100));
        expect(newState.leftAreaWidth).toBe(100);

        newState = designer(newState, saveSetting('testSetting', '100'));
        expect(newState.testSetting).toBe('100');
        expect(newState.testSetting).not.toBe(100);
    });

    test('designer default values', () => {
        localStorage.removeItem('designer');

        expect(designer(undefined, {})).toEqual({
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
        });
    });

    test('get data from localStorage', () => {
        localStorage.removeItem('designer');

        let newState = designer({}, saveSetting('leftAreaWidth', 100));

        newState = designer(undefined, {});

        expect(newState.leftAreaWidth).toBe(100);
    });

    test('save designer sub setting', () => {
        localStorage.removeItem('designer');
        let newState = designer({}, saveSubSetting('testField', 'testSubField', 200));
        expect(newState.testField.testSubField).toBe(200);
        expect(newState.testField.testSubField).not.toBe('200');
    });

    test('close tool, have to remove from area tool list and remove from active tool and set new active tool', () => {
        let state = {
            toolsList: {
                center: [{name: 'Themes'}, {name: 'Page'}],
            },
            activeTool: {
                center: 'Themes'
            }
        };
        let newState = designer(state, closeTool('center', 'Themes'));
        expect(newState.toolsList.center).not.toEqual(expect.arrayContaining([{name: 'Themes'}]));
        expect(newState.toolsList.center).toEqual(expect.arrayContaining([{name: 'Page'}]));
        expect(newState.activeTool.center).not.toBe('Themes');
        expect(newState.activeTool.center).toBe('Page');
    });

    test('move tool from area to popup, have to remove from area tool list, remove from active tool, set new active tool and appear in pupup list', () => {
        let state = {
            toolsList: {
                center: [{name: 'Themes'}, {name: 'Page'}],
                //toolWindows: [{name: 'Themes'}]
            },
            activeTool: {
                center: 'Themes'
            }
        };
        let newState = designer(state, toPopUp('center', 'Themes'));
        expect(newState.toolsList.center).not.toEqual(expect.arrayContaining([{name: 'Themes'}]));
        expect(newState.toolsList.center).toEqual(expect.arrayContaining([{name: 'Page'}]));
        expect(newState.activeTool.center).not.toBe('Themes');
        expect(newState.activeTool.center).toBe('Page');
        expect(newState.toolsList.toolWindows).toEqual(expect.arrayContaining([{name: 'Themes'}]));
    });

    test('move tool from popup to area, have to remove from pupup tool list, appear in center tool list, set it as active tool', () => {
        let state = {
            toolsList: {
                center: [{name: 'Page'}],
                toolWindows: [{name: 'Themes'}, {name: 'TestTool'}]
            },
            activeTool: {
                center: 'Themes'
            }
        };
        let newState = designer(state, fromPopUp('Themes'));
        expect(newState.toolsList.toolWindows).not.toEqual(expect.arrayContaining([{name: 'Themes'}]));
        expect(newState.toolsList.center).toEqual(expect.arrayContaining([{name: 'Themes'}, {name: 'Page'}]));
        expect(newState.activeTool.center).toBe('Themes');
        expect(newState.activeTool.center).not.toBe('Page');
        expect(newState.toolsList.toolWindows).not.toEqual(expect.arrayContaining([{name: 'Themes'}]));
        expect(newState.toolsList.toolWindows).toEqual(expect.arrayContaining([{name: 'TestTool'}]));
    });
})
