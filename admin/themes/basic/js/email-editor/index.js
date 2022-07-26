var emailEditor =
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./backend/email-editor/edit.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "../modules/popup-content-wraper.js":
/*!******************************************!*\
  !*** ../modules/popup-content-wraper.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var wrapper = function wrapper(content) {
    var buttons = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
    var heading = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;


    var allContent = '';
    if (heading) {
        allContent += '<div class="popup-heading">' + heading + '</div>';
    }
    allContent += '<div class="popup-content pop-mess-cont">' + content + '</div>';

    if (buttons.length > 0) {
        allContent += '<div class="popup-buttons">';
        buttons.forEach(function (item, i) {
            allContent += '<span class="btn ' + item.class + '">' + item.name + '</span>';
        });
        allContent += '</div>';
    }

    return allContent;
};
exports.default = wrapper;

/***/ }),

/***/ "./backend/email-editor/edit.js":
/*!**************************************!*\
  !*** ./backend/email-editor/edit.js ***!
  \**************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _style = __webpack_require__(/*! ./edit/style.scss */ "./backend/email-editor/edit/style.scss");

var _style2 = _interopRequireDefault(_style);

var _save = __webpack_require__(/*! ./edit/save */ "./backend/email-editor/edit/save.js");

var _save2 = _interopRequireDefault(_save);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

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

var emailEditor = function emailEditor(data) {

    var btnSave = $('.btn-save-boxes');
    var form = $('.email-editor');

    (0, _save2.default)({
        button: btnSave,
        form: form,
        saveUrl: 'email-editor/save'
    });
};

exports.emailEditor = emailEditor;

/***/ }),

/***/ "./backend/email-editor/edit/save.js":
/*!*******************************************!*\
  !*** ./backend/email-editor/edit/save.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _popupContentWraper = __webpack_require__(/*! src/popup-content-wraper */ "../modules/popup-content-wraper.js");

var _popupContentWraper2 = _interopRequireDefault(_popupContentWraper);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var save = function save() {
    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
        button: '',
        form: '',
        saveUrl: 'email-editor/save'
    };


    var savePage = function savePage() {
        var pageData = data.form.serializeArray();

        $.post(data.saveUrl, pageData, function (response) {

            var inputMapsId = $('input[name="maps_id"]', data.form);
            var mapsIdTmp = inputMapsId.val();

            inputMapsId.val(response.maps_id);

            if (mapsIdTmp !== response.maps_id) {
                window.history.pushState('', '', 'email-editor/edit?email_id=' + response.email_id);
            }

            if (response.status === 'ok') {
                alertMessage((0, _popupContentWraper2.default)(response.text));
                setTimeout(function () {
                    $('.popup-box-wrap:last').remove();
                }, 1000);
            }
        }, 'json');
    };

    data.button.on('click', savePage);
    data.form.on('submit', savePage);
};
exports.default = save;

/***/ }),

/***/ "./backend/email-editor/edit/style.scss":
/*!**********************************************!*\
  !*** ./backend/email-editor/edit/style.scss ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ })

/******/ });
//# sourceMappingURL=index.js.map