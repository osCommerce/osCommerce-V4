import { combineReducers } from 'redux';

import layout from './layout';
import topButtons from './topButtons';
import toolWindows from './toolWindows';
import designer from './designer';

export default combineReducers({ layout, topButtons, toolWindows, designer });