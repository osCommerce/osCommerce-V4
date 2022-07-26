import React from 'react';
import renderer from 'react-test-renderer';
import { Provider } from 'react-redux';
import configureStore from 'redux-mock-store';
import { act } from "react-dom/test-utils";

import Header from './Header';


const mockStore = configureStore([]);

describe('Header Connected', () => {
    let store;
    let component;

    beforeEach(() => {
        store = mockStore({
            layout: {
                sidebarWidth: 265,
                toggleSidebar: true,
                menuType: 'advanced',
                pageTitle: 'Themes'
            },
            topButtons: {
                addTheme: true
            },
            toolWindows: {
                addTheme: false
            },
            designer: {
            }
        });

        component = renderer.create(
            <Provider store={store}>
                <Header />
            </Provider>
        );
    });

    it('Header snapshot', () => {
        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should dispatch an action on button click', () => {

        const instance = component.getInstance()

        console.log(component.root);
        /*renderer.act(() => {
            component.root.findAllByProps('onToggleSidebar').props.onClick();
        });

        renderer.act(() => {
            component.root.findByType('input')
                .props.onChange({ target: { value: 'some other text' } });
        });

        expect(store.dispatch).toHaveBeenCalledTimes(1);
        expect(store.dispatch).toHaveBeenCalledWith(
            //myAction({ payload: 'some other text' })
        );*/
    });
});
