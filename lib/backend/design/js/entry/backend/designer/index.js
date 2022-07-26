/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux'
import { createStore, compose, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';

import Header from './components/Header';
import Footer from './components/Footer';
import Navigation from './components/Navigation';
import TopBar from './components/TopBar';
import Themes from './components/themes/Themes';
import Areas from './components/Areas';

import reducers from './reducers/';

import "../customize-bootstrap.scss";
import "../main.scss";
import './designer.scss';

const store = createStore(reducers, compose(
    applyMiddleware(thunk),
    window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
));

ReactDOM.render(
    <Provider store={store}>
        <div className="main-layout">
            <Header />
            <div className="main-wrapper">
                <Navigation />
                <div className="content-wrapper">
                    <TopBar />
                    <div className="content-container">
                        <div className="main-content">
                            <Areas />
                        </div>
                        <Footer />
                    </div>
                </div>
            </div>
        </div>
    </Provider>,
    document.getElementById('root')
);