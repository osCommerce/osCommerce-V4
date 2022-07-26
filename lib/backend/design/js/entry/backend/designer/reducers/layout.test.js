import layout from './layout'
import {toggleSidebar, resizeSidebar, toggleMenuType, setPageTitle} from './layout.actions'

describe('Testing layout reducer', () => {

    test('layout default values', () => {
        localStorage.removeItem('layout');

        expect(layout(undefined, {})).toEqual({
            sidebarWidth: 250,
            toggleSidebar: true,
        });
    });

    test('toggle sidebar', () => {
        localStorage.removeItem('layout');
        let newState = layout({}, toggleSidebar());
        expect(newState.toggleSidebar).toBe(true);
        newState = layout(newState, toggleSidebar());
        expect(newState.toggleSidebar).toBe(false);
    });

    test('resize sidebar, set sidebarWidth and get it form localStorage after reload', () => {
        localStorage.removeItem('layout');
        let newState = layout({}, resizeSidebar(100));
        expect(newState.sidebarWidth).toBe(100);
        newState = layout(undefined, {});
        expect(newState.sidebarWidth).toBe(100);
    });

    test('set menu type, set menuType and get it form localStorage after reload', () => {
        localStorage.removeItem('layout');
        let newState = layout({}, toggleMenuType('advanced'));
        expect(newState.menuType).toBe('advanced');
        newState = layout(undefined, {});
        expect(newState.menuType).toBe('advanced');
    });

    test('set page title, set pageTitle and get it form localStorage after reload', () => {
        localStorage.removeItem('layout');
        let newState = layout({}, setPageTitle('test'));
        expect(newState.pageTitle).toBe('test');
        newState = layout(undefined, {});
        expect(newState.pageTitle).toBe('test');
    });

})
