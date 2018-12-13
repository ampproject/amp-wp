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
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__ = __webpack_require__(1);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__ = __webpack_require__(15);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__);\nfunction _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }\n\n/**\n * WordPress dependencies\n */\n\n\n/**\n * Localized data\n */\n\n\nvar OPEN_CLASS = 'is-open';\n\n/**\n * Adds detail toggle buttons to the header and footer rows of the validation error \"details\" column.\n * The buttons are added via JS because there's no easy way to append them to the heading of a sortable\n * table column via backend code.\n *\n * @param {string} containerSelector Selector for elements that will have the button added.\n * @param {string} ariaLabel Screen reader label for the button.\n * @return {Array} Array of added buttons.\n */\nfunction addToggleButtons(containerSelector, ariaLabel) {\n\tvar addButton = function addButton(container) {\n\t\tvar button = document.createElement('button');\n\t\tbutton.setAttribute('aria-label', ariaLabel);\n\t\tbutton.setAttribute('type', 'button');\n\t\tbutton.setAttribute('class', 'error-details-toggle');\n\t\tcontainer.appendChild(button);\n\n\t\treturn button;\n\t};\n\n\treturn [].concat(_toConsumableArray(document.querySelectorAll(containerSelector))).map(function (container) {\n\t\treturn addButton(container);\n\t});\n}\n\nfunction addToggleAllListener(_ref) {\n\tvar btn = _ref.btn,\n\t    _ref$toggleAllButtonS = _ref.toggleAllButtonSelector,\n\t    toggleAllButtonSelector = _ref$toggleAllButtonS === undefined ? null : _ref$toggleAllButtonS,\n\t    targetDetailsSelector = _ref.targetDetailsSelector;\n\n\tvar open = false;\n\n\tvar targetDetails = [].concat(_toConsumableArray(document.querySelectorAll(targetDetailsSelector)));\n\n\tvar toggleAllButtons = [];\n\tif (toggleAllButtonSelector) {\n\t\ttoggleAllButtons = [].concat(_toConsumableArray(document.querySelectorAll(toggleAllButtonSelector)));\n\t}\n\n\tvar onButtonClick = function onButtonClick() {\n\t\topen = !open;\n\t\ttoggleAllButtons.forEach(function (toggleAllButton) {\n\t\t\ttoggleAllButton.classList.toggle(OPEN_CLASS);\n\t\t});\n\n\t\ttargetDetails.forEach(function (detail) {\n\t\t\tif (open) {\n\t\t\t\tdetail.setAttribute('open', true);\n\t\t\t} else {\n\t\t\t\tdetail.removeAttribute('open');\n\t\t\t}\n\t\t});\n\t};\n\n\tbtn.addEventListener('click', onButtonClick);\n}\n\n/**\n * Adds classes to the rows for the amp_validation_error term list table.\n *\n * This is needed because \\WP_Terms_List_Table::single_row() does not allow for additional\n * attributes to be added to the <tr> element.\n */\nfunction addTermListTableRowClasses() {\n\tvar rows = [].concat(_toConsumableArray(document.querySelectorAll('#the-list tr')));\n\trows.forEach(function (row) {\n\t\tvar statusText = row.querySelector('.column-status > .status-text');\n\t\tif (statusText) {\n\t\t\trow.classList.toggle('new', statusText.classList.contains('new'));\n\t\t\trow.classList.toggle('accepted', statusText.classList.contains('accepted'));\n\t\t\trow.classList.toggle('rejected', statusText.classList.contains('rejected'));\n\t\t}\n\t});\n}\n\nObject(__WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__[\"a\" /* default */])(function () {\n\taddToggleButtons('th.column-details.manage-column', __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__[\"detailToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-details button.error-details-toggle',\n\t\t\ttargetDetailsSelector: '.column-details details'\n\t\t});\n\t});\n\n\taddToggleButtons('th.manage-column.column-sources_with_invalid_output', __WEBPACK_IMPORTED_MODULE_1_amp_validation_i18n__[\"sourcesToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-sources_with_invalid_output button.error-details-toggle',\n\t\t\ttargetDetailsSelector: 'details.source'\n\t\t});\n\t});\n\n\taddTermListTableRowClasses();\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMTQuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLWRldGFpbC10b2dnbGUuanM/YmUxMiJdLCJzb3VyY2VzQ29udGVudCI6WyJmdW5jdGlvbiBfdG9Db25zdW1hYmxlQXJyYXkoYXJyKSB7IGlmIChBcnJheS5pc0FycmF5KGFycikpIHsgZm9yICh2YXIgaSA9IDAsIGFycjIgPSBBcnJheShhcnIubGVuZ3RoKTsgaSA8IGFyci5sZW5ndGg7IGkrKykgeyBhcnIyW2ldID0gYXJyW2ldOyB9IHJldHVybiBhcnIyOyB9IGVsc2UgeyByZXR1cm4gQXJyYXkuZnJvbShhcnIpOyB9IH1cblxuLyoqXG4gKiBXb3JkUHJlc3MgZGVwZW5kZW5jaWVzXG4gKi9cbmltcG9ydCBkb21SZWFkeSBmcm9tICdAd29yZHByZXNzL2RvbS1yZWFkeSc7XG5cbi8qKlxuICogTG9jYWxpemVkIGRhdGFcbiAqL1xuaW1wb3J0IHsgZGV0YWlsVG9nZ2xlQnRuQXJpYUxhYmVsLCBzb3VyY2VzVG9nZ2xlQnRuQXJpYUxhYmVsIH0gZnJvbSAnYW1wLXZhbGlkYXRpb24taTE4bic7XG5cbnZhciBPUEVOX0NMQVNTID0gJ2lzLW9wZW4nO1xuXG4vKipcbiAqIEFkZHMgZGV0YWlsIHRvZ2dsZSBidXR0b25zIHRvIHRoZSBoZWFkZXIgYW5kIGZvb3RlciByb3dzIG9mIHRoZSB2YWxpZGF0aW9uIGVycm9yIFwiZGV0YWlsc1wiIGNvbHVtbi5cbiAqIFRoZSBidXR0b25zIGFyZSBhZGRlZCB2aWEgSlMgYmVjYXVzZSB0aGVyZSdzIG5vIGVhc3kgd2F5IHRvIGFwcGVuZCB0aGVtIHRvIHRoZSBoZWFkaW5nIG9mIGEgc29ydGFibGVcbiAqIHRhYmxlIGNvbHVtbiB2aWEgYmFja2VuZCBjb2RlLlxuICpcbiAqIEBwYXJhbSB7c3RyaW5nfSBjb250YWluZXJTZWxlY3RvciBTZWxlY3RvciBmb3IgZWxlbWVudHMgdGhhdCB3aWxsIGhhdmUgdGhlIGJ1dHRvbiBhZGRlZC5cbiAqIEBwYXJhbSB7c3RyaW5nfSBhcmlhTGFiZWwgU2NyZWVuIHJlYWRlciBsYWJlbCBmb3IgdGhlIGJ1dHRvbi5cbiAqIEByZXR1cm4ge0FycmF5fSBBcnJheSBvZiBhZGRlZCBidXR0b25zLlxuICovXG5mdW5jdGlvbiBhZGRUb2dnbGVCdXR0b25zKGNvbnRhaW5lclNlbGVjdG9yLCBhcmlhTGFiZWwpIHtcblx0dmFyIGFkZEJ1dHRvbiA9IGZ1bmN0aW9uIGFkZEJ1dHRvbihjb250YWluZXIpIHtcblx0XHR2YXIgYnV0dG9uID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYnV0dG9uJyk7XG5cdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgnYXJpYS1sYWJlbCcsIGFyaWFMYWJlbCk7XG5cdFx0YnV0dG9uLnNldEF0dHJpYnV0ZSgndHlwZScsICdidXR0b24nKTtcblx0XHRidXR0b24uc2V0QXR0cmlidXRlKCdjbGFzcycsICdlcnJvci1kZXRhaWxzLXRvZ2dsZScpO1xuXHRcdGNvbnRhaW5lci5hcHBlbmRDaGlsZChidXR0b24pO1xuXG5cdFx0cmV0dXJuIGJ1dHRvbjtcblx0fTtcblxuXHRyZXR1cm4gW10uY29uY2F0KF90b0NvbnN1bWFibGVBcnJheShkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKGNvbnRhaW5lclNlbGVjdG9yKSkpLm1hcChmdW5jdGlvbiAoY29udGFpbmVyKSB7XG5cdFx0cmV0dXJuIGFkZEJ1dHRvbihjb250YWluZXIpO1xuXHR9KTtcbn1cblxuZnVuY3Rpb24gYWRkVG9nZ2xlQWxsTGlzdGVuZXIoX3JlZikge1xuXHR2YXIgYnRuID0gX3JlZi5idG4sXG5cdCAgICBfcmVmJHRvZ2dsZUFsbEJ1dHRvblMgPSBfcmVmLnRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yLFxuXHQgICAgdG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3IgPSBfcmVmJHRvZ2dsZUFsbEJ1dHRvblMgPT09IHVuZGVmaW5lZCA/IG51bGwgOiBfcmVmJHRvZ2dsZUFsbEJ1dHRvblMsXG5cdCAgICB0YXJnZXREZXRhaWxzU2VsZWN0b3IgPSBfcmVmLnRhcmdldERldGFpbHNTZWxlY3RvcjtcblxuXHR2YXIgb3BlbiA9IGZhbHNlO1xuXG5cdHZhciB0YXJnZXREZXRhaWxzID0gW10uY29uY2F0KF90b0NvbnN1bWFibGVBcnJheShkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHRhcmdldERldGFpbHNTZWxlY3RvcikpKTtcblxuXHR2YXIgdG9nZ2xlQWxsQnV0dG9ucyA9IFtdO1xuXHRpZiAodG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3IpIHtcblx0XHR0b2dnbGVBbGxCdXR0b25zID0gW10uY29uY2F0KF90b0NvbnN1bWFibGVBcnJheShkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yKSkpO1xuXHR9XG5cblx0dmFyIG9uQnV0dG9uQ2xpY2sgPSBmdW5jdGlvbiBvbkJ1dHRvbkNsaWNrKCkge1xuXHRcdG9wZW4gPSAhb3Blbjtcblx0XHR0b2dnbGVBbGxCdXR0b25zLmZvckVhY2goZnVuY3Rpb24gKHRvZ2dsZUFsbEJ1dHRvbikge1xuXHRcdFx0dG9nZ2xlQWxsQnV0dG9uLmNsYXNzTGlzdC50b2dnbGUoT1BFTl9DTEFTUyk7XG5cdFx0fSk7XG5cblx0XHR0YXJnZXREZXRhaWxzLmZvckVhY2goZnVuY3Rpb24gKGRldGFpbCkge1xuXHRcdFx0aWYgKG9wZW4pIHtcblx0XHRcdFx0ZGV0YWlsLnNldEF0dHJpYnV0ZSgnb3BlbicsIHRydWUpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0ZGV0YWlsLnJlbW92ZUF0dHJpYnV0ZSgnb3BlbicpO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9O1xuXG5cdGJ0bi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIG9uQnV0dG9uQ2xpY2spO1xufVxuXG4vKipcbiAqIEFkZHMgY2xhc3NlcyB0byB0aGUgcm93cyBmb3IgdGhlIGFtcF92YWxpZGF0aW9uX2Vycm9yIHRlcm0gbGlzdCB0YWJsZS5cbiAqXG4gKiBUaGlzIGlzIG5lZWRlZCBiZWNhdXNlIFxcV1BfVGVybXNfTGlzdF9UYWJsZTo6c2luZ2xlX3JvdygpIGRvZXMgbm90IGFsbG93IGZvciBhZGRpdGlvbmFsXG4gKiBhdHRyaWJ1dGVzIHRvIGJlIGFkZGVkIHRvIHRoZSA8dHI+IGVsZW1lbnQuXG4gKi9cbmZ1bmN0aW9uIGFkZFRlcm1MaXN0VGFibGVSb3dDbGFzc2VzKCkge1xuXHR2YXIgcm93cyA9IFtdLmNvbmNhdChfdG9Db25zdW1hYmxlQXJyYXkoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnI3RoZS1saXN0IHRyJykpKTtcblx0cm93cy5mb3JFYWNoKGZ1bmN0aW9uIChyb3cpIHtcblx0XHR2YXIgc3RhdHVzVGV4dCA9IHJvdy5xdWVyeVNlbGVjdG9yKCcuY29sdW1uLXN0YXR1cyA+IC5zdGF0dXMtdGV4dCcpO1xuXHRcdGlmIChzdGF0dXNUZXh0KSB7XG5cdFx0XHRyb3cuY2xhc3NMaXN0LnRvZ2dsZSgnbmV3Jywgc3RhdHVzVGV4dC5jbGFzc0xpc3QuY29udGFpbnMoJ25ldycpKTtcblx0XHRcdHJvdy5jbGFzc0xpc3QudG9nZ2xlKCdhY2NlcHRlZCcsIHN0YXR1c1RleHQuY2xhc3NMaXN0LmNvbnRhaW5zKCdhY2NlcHRlZCcpKTtcblx0XHRcdHJvdy5jbGFzc0xpc3QudG9nZ2xlKCdyZWplY3RlZCcsIHN0YXR1c1RleHQuY2xhc3NMaXN0LmNvbnRhaW5zKCdyZWplY3RlZCcpKTtcblx0XHR9XG5cdH0pO1xufVxuXG5kb21SZWFkeShmdW5jdGlvbiAoKSB7XG5cdGFkZFRvZ2dsZUJ1dHRvbnMoJ3RoLmNvbHVtbi1kZXRhaWxzLm1hbmFnZS1jb2x1bW4nLCBkZXRhaWxUb2dnbGVCdG5BcmlhTGFiZWwpLmZvckVhY2goZnVuY3Rpb24gKGJ0bikge1xuXHRcdGFkZFRvZ2dsZUFsbExpc3RlbmVyKHtcblx0XHRcdGJ0bjogYnRuLFxuXHRcdFx0dG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3I6ICcuY29sdW1uLWRldGFpbHMgYnV0dG9uLmVycm9yLWRldGFpbHMtdG9nZ2xlJyxcblx0XHRcdHRhcmdldERldGFpbHNTZWxlY3RvcjogJy5jb2x1bW4tZGV0YWlscyBkZXRhaWxzJ1xuXHRcdH0pO1xuXHR9KTtcblxuXHRhZGRUb2dnbGVCdXR0b25zKCd0aC5tYW5hZ2UtY29sdW1uLmNvbHVtbi1zb3VyY2VzX3dpdGhfaW52YWxpZF9vdXRwdXQnLCBzb3VyY2VzVG9nZ2xlQnRuQXJpYUxhYmVsKS5mb3JFYWNoKGZ1bmN0aW9uIChidG4pIHtcblx0XHRhZGRUb2dnbGVBbGxMaXN0ZW5lcih7XG5cdFx0XHRidG46IGJ0bixcblx0XHRcdHRvZ2dsZUFsbEJ1dHRvblNlbGVjdG9yOiAnLmNvbHVtbi1zb3VyY2VzX3dpdGhfaW52YWxpZF9vdXRwdXQgYnV0dG9uLmVycm9yLWRldGFpbHMtdG9nZ2xlJyxcblx0XHRcdHRhcmdldERldGFpbHNTZWxlY3RvcjogJ2RldGFpbHMuc291cmNlJ1xuXHRcdH0pO1xuXHR9KTtcblxuXHRhZGRUZXJtTGlzdFRhYmxlUm93Q2xhc3NlcygpO1xufSk7XG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLWRldGFpbC10b2dnbGUuanNcbi8vIG1vZHVsZSBpZCA9IDE0XG4vLyBtb2R1bGUgY2h1bmtzID0gMSJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///14\n");

/***/ }),

/***/ 15:
/***/ (function(module, exports) {

module.exports = ampValidationI18n;

/***/ })

/******/ });