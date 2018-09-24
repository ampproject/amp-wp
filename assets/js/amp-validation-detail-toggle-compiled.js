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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 14);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("/**\n * Specify a function to execute when the DOM is fully loaded.\n *\n * @param {Function} callback A function to execute after the DOM is ready.\n *\n * @return {void}\n */\nvar domReady = function domReady(callback) {\n  if (document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.\n  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.\n  ) {\n      return callback();\n    } // DOMContentLoaded has not fired yet, delay callback until then.\n\n\n  document.addEventListener('DOMContentLoaded', callback);\n};\n\n/* harmony default export */ __webpack_exports__[\"a\"] = (domReady);\n//# sourceMappingURL=index.js.map//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMS5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9Ad29yZHByZXNzL2RvbS1yZWFkeS9idWlsZC1tb2R1bGUvaW5kZXguanM/YTc2MyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFNwZWNpZnkgYSBmdW5jdGlvbiB0byBleGVjdXRlIHdoZW4gdGhlIERPTSBpcyBmdWxseSBsb2FkZWQuXG4gKlxuICogQHBhcmFtIHtGdW5jdGlvbn0gY2FsbGJhY2sgQSBmdW5jdGlvbiB0byBleGVjdXRlIGFmdGVyIHRoZSBET00gaXMgcmVhZHkuXG4gKlxuICogQHJldHVybiB7dm9pZH1cbiAqL1xudmFyIGRvbVJlYWR5ID0gZnVuY3Rpb24gZG9tUmVhZHkoY2FsbGJhY2spIHtcbiAgaWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdjb21wbGV0ZScgfHwgLy8gRE9NQ29udGVudExvYWRlZCArIEltYWdlcy9TdHlsZXMvZXRjIGxvYWRlZCwgc28gd2UgY2FsbCBkaXJlY3RseS5cbiAgZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2ludGVyYWN0aXZlJyAvLyBET01Db250ZW50TG9hZGVkIGZpcmVzIGF0IHRoaXMgcG9pbnQsIHNvIHdlIGNhbGwgZGlyZWN0bHkuXG4gICkge1xuICAgICAgcmV0dXJuIGNhbGxiYWNrKCk7XG4gICAgfSAvLyBET01Db250ZW50TG9hZGVkIGhhcyBub3QgZmlyZWQgeWV0LCBkZWxheSBjYWxsYmFjayB1bnRpbCB0aGVuLlxuXG5cbiAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGNhbGxiYWNrKTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGRvbVJlYWR5O1xuLy8jIHNvdXJjZU1hcHBpbmdVUkw9aW5kZXguanMubWFwXG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9ub2RlX21vZHVsZXMvQHdvcmRwcmVzcy9kb20tcmVhZHkvYnVpbGQtbW9kdWxlL2luZGV4LmpzXG4vLyBtb2R1bGUgaWQgPSAxXG4vLyBtb2R1bGUgY2h1bmtzID0gMSAyIDMiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///1\n");

/***/ }),

/***/ 14:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__ = __webpack_require__(1);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__ = __webpack_require__(15);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__);\nfunction _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }\n\n/**\n * WordPress dependencies\n */\n\n\n/**\n * Localized data\n */\n\n\nvar OPEN_CLASS = 'is-open';\n\n/**\n * Adds detail toggle buttons to the header and footer rows of the validation error \"details\" column.\n * The buttons are added via JS because there's no easy way to append them to the heading of a sortable\n * table column via backend code.\n * \n * @param {string} containerSelector Selector for elements that will have the button added.\n * @param {string} ariaLabel Screen reader label for the button.\n * @return {Array} Array of added buttons.\n */\nfunction addToggleButtons(containerSelector, ariaLabel) {\n\tvar addButton = function addButton(container) {\n\t\tvar button = document.createElement('button');\n\t\tbutton.setAttribute('aria-label', ariaLabel);\n\t\tbutton.setAttribute('type', 'button');\n\t\tbutton.setAttribute('class', 'error-details-toggle');\n\t\tcontainer.appendChild(button);\n\n\t\treturn button;\n\t};\n\n\treturn [].concat(_toConsumableArray(document.querySelectorAll(containerSelector))).map(function (container) {\n\t\treturn addButton(container);\n\t});\n}\n\nfunction addToggleAllListener(_ref) {\n\tvar btn = _ref.btn,\n\t    _ref$toggleAllButtonS = _ref.toggleAllButtonSelector,\n\t    toggleAllButtonSelector = _ref$toggleAllButtonS === undefined ? null : _ref$toggleAllButtonS,\n\t    targetDetailsSelector = _ref.targetDetailsSelector;\n\n\tvar open = false;\n\n\tvar targetDetails = [].concat(_toConsumableArray(document.querySelectorAll(targetDetailsSelector)));\n\n\tvar toggleAllButtons = [];\n\tif (toggleAllButtonSelector) {\n\t\ttoggleAllButtons = [].concat(_toConsumableArray(document.querySelectorAll(toggleAllButtonSelector)));\n\t}\n\n\tvar onButtonClick = function onButtonClick() {\n\t\topen = !open;\n\t\ttoggleAllButtons.forEach(function (toggleAllButton) {\n\t\t\ttoggleAllButton.classList.toggle(OPEN_CLASS);\n\t\t});\n\n\t\ttargetDetails.forEach(function (detail) {\n\t\t\tif (open) {\n\t\t\t\tdetail.setAttribute('open', true);\n\t\t\t} else {\n\t\t\t\tdetail.removeAttribute('open');\n\t\t\t}\n\t\t});\n\t};\n\n\tbtn.addEventListener('click', onButtonClick);\n}\n\nObject(__WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__[\"a\" /* default */])(function () {\n\taddToggleButtons('th.column-details.manage-column', __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__[\"detailToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-details button.error-details-toggle',\n\t\t\ttargetDetailsSelector: '.column-details details'\n\t\t});\n\t});\n\n\taddToggleButtons('th.manage-column.column-sources_with_invalid_output', __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__[\"sourcesToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-sources_with_invalid_output button.error-details-toggle',\n\t\t\ttargetDetailsSelector: 'details.source'\n\t\t});\n\t});\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMTQuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLWRldGFpbC10b2dnbGUuanM/YmUxMiJdLCJzb3VyY2VzQ29udGVudCI6WyJmdW5jdGlvbiBfdG9Db25zdW1hYmxlQXJyYXkoYXJyKSB7IGlmIChBcnJheS5pc0FycmF5KGFycikpIHsgZm9yICh2YXIgaSA9IDAsIGFycjIgPSBBcnJheShhcnIubGVuZ3RoKTsgaSA8IGFyci5sZW5ndGg7IGkrKykgeyBhcnIyW2ldID0gYXJyW2ldOyB9IHJldHVybiBhcnIyOyB9IGVsc2UgeyByZXR1cm4gQXJyYXkuZnJvbShhcnIpOyB9IH1cblxuLyoqXG4gKiBXb3JkUHJlc3MgZGVwZW5kZW5jaWVzXG4gKi9cbmltcG9ydCBkb21SZWFkeSBmcm9tICdAd29yZHByZXNzL2RvbS1yZWFkeSc7XG5cbi8qKlxuICogTG9jYWxpemVkIGRhdGFcbiAqL1xuaW1wb3J0IHsgZGV0YWlsVG9nZ2xlQnRuQXJpYUxhYmVsLCBzb3VyY2VzVG9nZ2xlQnRuQXJpYUxhYmVsIH0gZnJvbSAnYW1wLXZhbGlkYXRpb24taTE4bic7XG5cbnZhciBPUEVOX0NMQVNTID0gJ2lzLW9wZW4nO1xuXG4vKipcbiAqIEFkZHMgZGV0YWlsIHRvZ2dsZSBidXR0b25zIHRvIHRoZSBoZWFkZXIgYW5kIGZvb3RlciByb3dzIG9mIHRoZSB2YWxpZGF0aW9uIGVycm9yIFwiZGV0YWlsc1wiIGNvbHVtbi5cbiAqIFRoZSBidXR0b25zIGFyZSBhZGRlZCB2aWEgSlMgYmVjYXVzZSB0aGVyZSdzIG5vIGVhc3kgd2F5IHRvIGFwcGVuZCB0aGVtIHRvIHRoZSBoZWFkaW5nIG9mIGEgc29ydGFibGVcbiAqIHRhYmxlIGNvbHVtbiB2aWEgYmFja2VuZCBjb2RlLlxuICogXG4gKiBAcGFyYW0ge3N0cmluZ30gY29udGFpbmVyU2VsZWN0b3IgU2VsZWN0b3IgZm9yIGVsZW1lbnRzIHRoYXQgd2lsbCBoYXZlIHRoZSBidXR0b24gYWRkZWQuXG4gKiBAcGFyYW0ge3N0cmluZ30gYXJpYUxhYmVsIFNjcmVlbiByZWFkZXIgbGFiZWwgZm9yIHRoZSBidXR0b24uXG4gKiBAcmV0dXJuIHtBcnJheX0gQXJyYXkgb2YgYWRkZWQgYnV0dG9ucy5cbiAqL1xuZnVuY3Rpb24gYWRkVG9nZ2xlQnV0dG9ucyhjb250YWluZXJTZWxlY3RvciwgYXJpYUxhYmVsKSB7XG5cdHZhciBhZGRCdXR0b24gPSBmdW5jdGlvbiBhZGRCdXR0b24oY29udGFpbmVyKSB7XG5cdFx0dmFyIGJ1dHRvbiA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2J1dHRvbicpO1xuXHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2FyaWEtbGFiZWwnLCBhcmlhTGFiZWwpO1xuXHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ3R5cGUnLCAnYnV0dG9uJyk7XG5cdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgnY2xhc3MnLCAnZXJyb3ItZGV0YWlscy10b2dnbGUnKTtcblx0XHRjb250YWluZXIuYXBwZW5kQ2hpbGQoYnV0dG9uKTtcblxuXHRcdHJldHVybiBidXR0b247XG5cdH07XG5cblx0cmV0dXJuIFtdLmNvbmNhdChfdG9Db25zdW1hYmxlQXJyYXkoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChjb250YWluZXJTZWxlY3RvcikpKS5tYXAoZnVuY3Rpb24gKGNvbnRhaW5lcikge1xuXHRcdHJldHVybiBhZGRCdXR0b24oY29udGFpbmVyKTtcblx0fSk7XG59XG5cbmZ1bmN0aW9uIGFkZFRvZ2dsZUFsbExpc3RlbmVyKF9yZWYpIHtcblx0dmFyIGJ0biA9IF9yZWYuYnRuLFxuXHQgICAgX3JlZiR0b2dnbGVBbGxCdXR0b25TID0gX3JlZi50b2dnbGVBbGxCdXR0b25TZWxlY3Rvcixcblx0ICAgIHRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yID0gX3JlZiR0b2dnbGVBbGxCdXR0b25TID09PSB1bmRlZmluZWQgPyBudWxsIDogX3JlZiR0b2dnbGVBbGxCdXR0b25TLFxuXHQgICAgdGFyZ2V0RGV0YWlsc1NlbGVjdG9yID0gX3JlZi50YXJnZXREZXRhaWxzU2VsZWN0b3I7XG5cblx0dmFyIG9wZW4gPSBmYWxzZTtcblxuXHR2YXIgdGFyZ2V0RGV0YWlscyA9IFtdLmNvbmNhdChfdG9Db25zdW1hYmxlQXJyYXkoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCh0YXJnZXREZXRhaWxzU2VsZWN0b3IpKSk7XG5cblx0dmFyIHRvZ2dsZUFsbEJ1dHRvbnMgPSBbXTtcblx0aWYgKHRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yKSB7XG5cdFx0dG9nZ2xlQWxsQnV0dG9ucyA9IFtdLmNvbmNhdChfdG9Db25zdW1hYmxlQXJyYXkoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCh0b2dnbGVBbGxCdXR0b25TZWxlY3RvcikpKTtcblx0fVxuXG5cdHZhciBvbkJ1dHRvbkNsaWNrID0gZnVuY3Rpb24gb25CdXR0b25DbGljaygpIHtcblx0XHRvcGVuID0gIW9wZW47XG5cdFx0dG9nZ2xlQWxsQnV0dG9ucy5mb3JFYWNoKGZ1bmN0aW9uICh0b2dnbGVBbGxCdXR0b24pIHtcblx0XHRcdHRvZ2dsZUFsbEJ1dHRvbi5jbGFzc0xpc3QudG9nZ2xlKE9QRU5fQ0xBU1MpO1xuXHRcdH0pO1xuXG5cdFx0dGFyZ2V0RGV0YWlscy5mb3JFYWNoKGZ1bmN0aW9uIChkZXRhaWwpIHtcblx0XHRcdGlmIChvcGVuKSB7XG5cdFx0XHRcdGRldGFpbC5zZXRBdHRyaWJ1dGUoJ29wZW4nLCB0cnVlKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGRldGFpbC5yZW1vdmVBdHRyaWJ1dGUoJ29wZW4nKTtcblx0XHRcdH1cblx0XHR9KTtcblx0fTtcblxuXHRidG4uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBvbkJ1dHRvbkNsaWNrKTtcbn1cblxuZG9tUmVhZHkoZnVuY3Rpb24gKCkge1xuXHRhZGRUb2dnbGVCdXR0b25zKCd0aC5jb2x1bW4tZGV0YWlscy5tYW5hZ2UtY29sdW1uJywgZGV0YWlsVG9nZ2xlQnRuQXJpYUxhYmVsKS5mb3JFYWNoKGZ1bmN0aW9uIChidG4pIHtcblx0XHRhZGRUb2dnbGVBbGxMaXN0ZW5lcih7XG5cdFx0XHRidG46IGJ0bixcblx0XHRcdHRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yOiAnLmNvbHVtbi1kZXRhaWxzIGJ1dHRvbi5lcnJvci1kZXRhaWxzLXRvZ2dsZScsXG5cdFx0XHR0YXJnZXREZXRhaWxzU2VsZWN0b3I6ICcuY29sdW1uLWRldGFpbHMgZGV0YWlscydcblx0XHR9KTtcblx0fSk7XG5cblx0YWRkVG9nZ2xlQnV0dG9ucygndGgubWFuYWdlLWNvbHVtbi5jb2x1bW4tc291cmNlc193aXRoX2ludmFsaWRfb3V0cHV0Jywgc291cmNlc1RvZ2dsZUJ0bkFyaWFMYWJlbCkuZm9yRWFjaChmdW5jdGlvbiAoYnRuKSB7XG5cdFx0YWRkVG9nZ2xlQWxsTGlzdGVuZXIoe1xuXHRcdFx0YnRuOiBidG4sXG5cdFx0XHR0b2dnbGVBbGxCdXR0b25TZWxlY3RvcjogJy5jb2x1bW4tc291cmNlc193aXRoX2ludmFsaWRfb3V0cHV0IGJ1dHRvbi5lcnJvci1kZXRhaWxzLXRvZ2dsZScsXG5cdFx0XHR0YXJnZXREZXRhaWxzU2VsZWN0b3I6ICdkZXRhaWxzLnNvdXJjZSdcblx0XHR9KTtcblx0fSk7XG59KTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Fzc2V0cy9zcmMvYW1wLXZhbGlkYXRpb24tZGV0YWlsLXRvZ2dsZS5qc1xuLy8gbW9kdWxlIGlkID0gMTRcbi8vIG1vZHVsZSBjaHVua3MgPSAxIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///14\n");

/***/ }),

/***/ 15:
/***/ (function(module, exports) {

module.exports = ampValidationI18n;

/***/ })

/******/ });