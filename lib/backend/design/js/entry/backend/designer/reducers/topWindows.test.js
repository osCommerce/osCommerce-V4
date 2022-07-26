import toolWindows from './toolWindows'
import {showWindow, hideWindow, toggleWindow} from './toolWindows.actions'

describe('Testing toolWindows reducer', () => {

    test('toolWindows default values', () => {
        expect(toolWindows(undefined, {})).toEqual({
            addTheme: false
        });
    });

    test('show window', () => {
        let newState = toolWindows({}, showWindow('addTheme'));
        expect(newState['addTheme']).toBe(true);
    });

    test('hide window', () => {
        let newState = toolWindows({}, hideWindow('addTheme'));
        expect(newState['addTheme']).toBe(false);
    });

    test('toggle window', () => {
        let newState = toolWindows({}, toggleWindow('addTheme'));
        expect(newState['addTheme']).toBe(true);
        newState = toolWindows(newState, toggleWindow('addTheme'));
        expect(newState['addTheme']).toBe(false);
    });

})
