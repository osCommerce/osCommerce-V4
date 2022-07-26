import topButtons from './topButtons'
import {showButton, hideButton, toggleButton} from './topButtons.actions'

describe('Testing topButtons reducer', () => {

    test('topButtons default values', () => {
        expect(topButtons(undefined, {})).toEqual({
            addTheme: false
        });
    });

    test('show button', () => {
        let newState = topButtons({}, showButton('addTheme'));
        expect(newState['addTheme']).toBe(true);
    });

    test('hide button', () => {
        let newState = topButtons({}, hideButton('addTheme'));
        expect(newState['addTheme']).toBe(false);
    });

    test('toggle button', () => {
        let newState = topButtons({}, toggleButton('addTheme'));
        expect(newState['addTheme']).toBe(true);
        newState = topButtons(newState, toggleButton('addTheme'));
        expect(newState['addTheme']).toBe(false);
    });

})
