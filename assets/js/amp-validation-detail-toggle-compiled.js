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
/******/ 	return __webpack_require__(__webpack_require__.s = 76);
/******/ })
/************************************************************************/
/******/ ({

/***/ 76:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_amp_validation_i18n__ = __webpack_require__(77);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_amp_validation_i18n___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_amp_validation_i18n__);\nfunction _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }\n\n/**\n * Localized data\n */\n\n\nvar OPEN_CLASS = 'is-open';\n\n/**\n * Adds detail toggle buttons to the header and footer rows of the validation error \"details\" column.\n * The buttons are added via JS because there's no easy way to append them to the heading of a sortable\n * table column via backend code.\n *\n * @param {string} containerSelector Selector for elements that will have the button added.\n * @param {string} ariaLabel Screen reader label for the button.\n * @return {Array} Array of added buttons.\n */\nfunction addToggleButtons(containerSelector, ariaLabel) {\n\tvar addButton = function addButton(container) {\n\t\tvar button = document.createElement('button');\n\t\tbutton.setAttribute('aria-label', ariaLabel);\n\t\tbutton.setAttribute('type', 'button');\n\t\tbutton.setAttribute('class', 'error-details-toggle');\n\t\tcontainer.appendChild(button);\n\n\t\treturn button;\n\t};\n\n\treturn [].concat(_toConsumableArray(document.querySelectorAll(containerSelector))).map(function (container) {\n\t\treturn addButton(container);\n\t});\n}\n\nfunction addToggleAllListener(_ref) {\n\tvar btn = _ref.btn,\n\t    _ref$toggleAllButtonS = _ref.toggleAllButtonSelector,\n\t    toggleAllButtonSelector = _ref$toggleAllButtonS === undefined ? null : _ref$toggleAllButtonS,\n\t    targetDetailsSelector = _ref.targetDetailsSelector;\n\n\tvar open = false;\n\n\tvar targetDetails = [].concat(_toConsumableArray(document.querySelectorAll(targetDetailsSelector)));\n\n\tvar toggleAllButtons = [];\n\tif (toggleAllButtonSelector) {\n\t\ttoggleAllButtons = [].concat(_toConsumableArray(document.querySelectorAll(toggleAllButtonSelector)));\n\t}\n\n\tvar onButtonClick = function onButtonClick() {\n\t\topen = !open;\n\t\ttoggleAllButtons.forEach(function (toggleAllButton) {\n\t\t\ttoggleAllButton.classList.toggle(OPEN_CLASS);\n\t\t});\n\n\t\ttargetDetails.forEach(function (detail) {\n\t\t\tif (open) {\n\t\t\t\tdetail.setAttribute('open', true);\n\t\t\t} else {\n\t\t\t\tdetail.removeAttribute('open');\n\t\t\t}\n\t\t});\n\t};\n\n\tbtn.addEventListener('click', onButtonClick);\n}\n\n/**\n * Adds classes to the rows for the amp_validation_error term list table.\n *\n * This is needed because \\WP_Terms_List_Table::single_row() does not allow for additional\n * attributes to be added to the <tr> element.\n */\nfunction addTermListTableRowClasses() {\n\tvar rows = [].concat(_toConsumableArray(document.querySelectorAll('#the-list tr')));\n\trows.forEach(function (row) {\n\t\tvar statusText = row.querySelector('.column-status > .status-text');\n\t\tif (statusText) {\n\t\t\trow.classList.toggle('new', statusText.classList.contains('new'));\n\t\t\trow.classList.toggle('accepted', statusText.classList.contains('accepted'));\n\t\t\trow.classList.toggle('rejected', statusText.classList.contains('rejected'));\n\t\t}\n\t});\n}\n\nwp.domReady(function () {\n\taddToggleButtons('th.column-details.manage-column', __WEBPACK_IMPORTED_MODULE_0_amp_validation_i18n__[\"detailToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-details button.error-details-toggle',\n\t\t\ttargetDetailsSelector: '.column-details details'\n\t\t});\n\t});\n\n\taddToggleButtons('th.manage-column.column-sources_with_invalid_output', __WEBPACK_IMPORTED_MODULE_0_amp_validation_i18n__[\"sourcesToggleBtnAriaLabel\"]).forEach(function (btn) {\n\t\taddToggleAllListener({\n\t\t\tbtn: btn,\n\t\t\ttoggleAllButtonSelector: '.column-sources_with_invalid_output button.error-details-toggle',\n\t\t\ttargetDetailsSelector: 'details.source'\n\t\t});\n\t});\n\n\taddTermListTableRowClasses();\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiNzYuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLWRldGFpbC10b2dnbGUuanM/YmUxMiJdLCJzb3VyY2VzQ29udGVudCI6WyJmdW5jdGlvbiBfdG9Db25zdW1hYmxlQXJyYXkoYXJyKSB7IGlmIChBcnJheS5pc0FycmF5KGFycikpIHsgZm9yICh2YXIgaSA9IDAsIGFycjIgPSBBcnJheShhcnIubGVuZ3RoKTsgaSA8IGFyci5sZW5ndGg7IGkrKykgeyBhcnIyW2ldID0gYXJyW2ldOyB9IHJldHVybiBhcnIyOyB9IGVsc2UgeyByZXR1cm4gQXJyYXkuZnJvbShhcnIpOyB9IH1cblxuLyoqXG4gKiBMb2NhbGl6ZWQgZGF0YVxuICovXG5pbXBvcnQgeyBkZXRhaWxUb2dnbGVCdG5BcmlhTGFiZWwsIHNvdXJjZXNUb2dnbGVCdG5BcmlhTGFiZWwgfSBmcm9tICdhbXAtdmFsaWRhdGlvbi1pMThuJztcblxudmFyIE9QRU5fQ0xBU1MgPSAnaXMtb3Blbic7XG5cbi8qKlxuICogQWRkcyBkZXRhaWwgdG9nZ2xlIGJ1dHRvbnMgdG8gdGhlIGhlYWRlciBhbmQgZm9vdGVyIHJvd3Mgb2YgdGhlIHZhbGlkYXRpb24gZXJyb3IgXCJkZXRhaWxzXCIgY29sdW1uLlxuICogVGhlIGJ1dHRvbnMgYXJlIGFkZGVkIHZpYSBKUyBiZWNhdXNlIHRoZXJlJ3Mgbm8gZWFzeSB3YXkgdG8gYXBwZW5kIHRoZW0gdG8gdGhlIGhlYWRpbmcgb2YgYSBzb3J0YWJsZVxuICogdGFibGUgY29sdW1uIHZpYSBiYWNrZW5kIGNvZGUuXG4gKlxuICogQHBhcmFtIHtzdHJpbmd9IGNvbnRhaW5lclNlbGVjdG9yIFNlbGVjdG9yIGZvciBlbGVtZW50cyB0aGF0IHdpbGwgaGF2ZSB0aGUgYnV0dG9uIGFkZGVkLlxuICogQHBhcmFtIHtzdHJpbmd9IGFyaWFMYWJlbCBTY3JlZW4gcmVhZGVyIGxhYmVsIGZvciB0aGUgYnV0dG9uLlxuICogQHJldHVybiB7QXJyYXl9IEFycmF5IG9mIGFkZGVkIGJ1dHRvbnMuXG4gKi9cbmZ1bmN0aW9uIGFkZFRvZ2dsZUJ1dHRvbnMoY29udGFpbmVyU2VsZWN0b3IsIGFyaWFMYWJlbCkge1xuXHR2YXIgYWRkQnV0dG9uID0gZnVuY3Rpb24gYWRkQnV0dG9uKGNvbnRhaW5lcikge1xuXHRcdHZhciBidXR0b24gPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdidXR0b24nKTtcblx0XHRidXR0b24uc2V0QXR0cmlidXRlKCdhcmlhLWxhYmVsJywgYXJpYUxhYmVsKTtcblx0XHRidXR0b24uc2V0QXR0cmlidXRlKCd0eXBlJywgJ2J1dHRvbicpO1xuXHRcdGJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgJ2Vycm9yLWRldGFpbHMtdG9nZ2xlJyk7XG5cdFx0Y29udGFpbmVyLmFwcGVuZENoaWxkKGJ1dHRvbik7XG5cblx0XHRyZXR1cm4gYnV0dG9uO1xuXHR9O1xuXG5cdHJldHVybiBbXS5jb25jYXQoX3RvQ29uc3VtYWJsZUFycmF5KGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoY29udGFpbmVyU2VsZWN0b3IpKSkubWFwKGZ1bmN0aW9uIChjb250YWluZXIpIHtcblx0XHRyZXR1cm4gYWRkQnV0dG9uKGNvbnRhaW5lcik7XG5cdH0pO1xufVxuXG5mdW5jdGlvbiBhZGRUb2dnbGVBbGxMaXN0ZW5lcihfcmVmKSB7XG5cdHZhciBidG4gPSBfcmVmLmJ0bixcblx0ICAgIF9yZWYkdG9nZ2xlQWxsQnV0dG9uUyA9IF9yZWYudG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3IsXG5cdCAgICB0b2dnbGVBbGxCdXR0b25TZWxlY3RvciA9IF9yZWYkdG9nZ2xlQWxsQnV0dG9uUyA9PT0gdW5kZWZpbmVkID8gbnVsbCA6IF9yZWYkdG9nZ2xlQWxsQnV0dG9uUyxcblx0ICAgIHRhcmdldERldGFpbHNTZWxlY3RvciA9IF9yZWYudGFyZ2V0RGV0YWlsc1NlbGVjdG9yO1xuXG5cdHZhciBvcGVuID0gZmFsc2U7XG5cblx0dmFyIHRhcmdldERldGFpbHMgPSBbXS5jb25jYXQoX3RvQ29uc3VtYWJsZUFycmF5KGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwodGFyZ2V0RGV0YWlsc1NlbGVjdG9yKSkpO1xuXG5cdHZhciB0b2dnbGVBbGxCdXR0b25zID0gW107XG5cdGlmICh0b2dnbGVBbGxCdXR0b25TZWxlY3Rvcikge1xuXHRcdHRvZ2dsZUFsbEJ1dHRvbnMgPSBbXS5jb25jYXQoX3RvQ29uc3VtYWJsZUFycmF5KGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwodG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3IpKSk7XG5cdH1cblxuXHR2YXIgb25CdXR0b25DbGljayA9IGZ1bmN0aW9uIG9uQnV0dG9uQ2xpY2soKSB7XG5cdFx0b3BlbiA9ICFvcGVuO1xuXHRcdHRvZ2dsZUFsbEJ1dHRvbnMuZm9yRWFjaChmdW5jdGlvbiAodG9nZ2xlQWxsQnV0dG9uKSB7XG5cdFx0XHR0b2dnbGVBbGxCdXR0b24uY2xhc3NMaXN0LnRvZ2dsZShPUEVOX0NMQVNTKTtcblx0XHR9KTtcblxuXHRcdHRhcmdldERldGFpbHMuZm9yRWFjaChmdW5jdGlvbiAoZGV0YWlsKSB7XG5cdFx0XHRpZiAob3Blbikge1xuXHRcdFx0XHRkZXRhaWwuc2V0QXR0cmlidXRlKCdvcGVuJywgdHJ1ZSk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRkZXRhaWwucmVtb3ZlQXR0cmlidXRlKCdvcGVuJyk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdH07XG5cblx0YnRuLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgb25CdXR0b25DbGljayk7XG59XG5cbi8qKlxuICogQWRkcyBjbGFzc2VzIHRvIHRoZSByb3dzIGZvciB0aGUgYW1wX3ZhbGlkYXRpb25fZXJyb3IgdGVybSBsaXN0IHRhYmxlLlxuICpcbiAqIFRoaXMgaXMgbmVlZGVkIGJlY2F1c2UgXFxXUF9UZXJtc19MaXN0X1RhYmxlOjpzaW5nbGVfcm93KCkgZG9lcyBub3QgYWxsb3cgZm9yIGFkZGl0aW9uYWxcbiAqIGF0dHJpYnV0ZXMgdG8gYmUgYWRkZWQgdG8gdGhlIDx0cj4gZWxlbWVudC5cbiAqL1xuZnVuY3Rpb24gYWRkVGVybUxpc3RUYWJsZVJvd0NsYXNzZXMoKSB7XG5cdHZhciByb3dzID0gW10uY29uY2F0KF90b0NvbnN1bWFibGVBcnJheShkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcjdGhlLWxpc3QgdHInKSkpO1xuXHRyb3dzLmZvckVhY2goZnVuY3Rpb24gKHJvdykge1xuXHRcdHZhciBzdGF0dXNUZXh0ID0gcm93LnF1ZXJ5U2VsZWN0b3IoJy5jb2x1bW4tc3RhdHVzID4gLnN0YXR1cy10ZXh0Jyk7XG5cdFx0aWYgKHN0YXR1c1RleHQpIHtcblx0XHRcdHJvdy5jbGFzc0xpc3QudG9nZ2xlKCduZXcnLCBzdGF0dXNUZXh0LmNsYXNzTGlzdC5jb250YWlucygnbmV3JykpO1xuXHRcdFx0cm93LmNsYXNzTGlzdC50b2dnbGUoJ2FjY2VwdGVkJywgc3RhdHVzVGV4dC5jbGFzc0xpc3QuY29udGFpbnMoJ2FjY2VwdGVkJykpO1xuXHRcdFx0cm93LmNsYXNzTGlzdC50b2dnbGUoJ3JlamVjdGVkJywgc3RhdHVzVGV4dC5jbGFzc0xpc3QuY29udGFpbnMoJ3JlamVjdGVkJykpO1xuXHRcdH1cblx0fSk7XG59XG5cbndwLmRvbVJlYWR5KGZ1bmN0aW9uICgpIHtcblx0YWRkVG9nZ2xlQnV0dG9ucygndGguY29sdW1uLWRldGFpbHMubWFuYWdlLWNvbHVtbicsIGRldGFpbFRvZ2dsZUJ0bkFyaWFMYWJlbCkuZm9yRWFjaChmdW5jdGlvbiAoYnRuKSB7XG5cdFx0YWRkVG9nZ2xlQWxsTGlzdGVuZXIoe1xuXHRcdFx0YnRuOiBidG4sXG5cdFx0XHR0b2dnbGVBbGxCdXR0b25TZWxlY3RvcjogJy5jb2x1bW4tZGV0YWlscyBidXR0b24uZXJyb3ItZGV0YWlscy10b2dnbGUnLFxuXHRcdFx0dGFyZ2V0RGV0YWlsc1NlbGVjdG9yOiAnLmNvbHVtbi1kZXRhaWxzIGRldGFpbHMnXG5cdFx0fSk7XG5cdH0pO1xuXG5cdGFkZFRvZ2dsZUJ1dHRvbnMoJ3RoLm1hbmFnZS1jb2x1bW4uY29sdW1uLXNvdXJjZXNfd2l0aF9pbnZhbGlkX291dHB1dCcsIHNvdXJjZXNUb2dnbGVCdG5BcmlhTGFiZWwpLmZvckVhY2goZnVuY3Rpb24gKGJ0bikge1xuXHRcdGFkZFRvZ2dsZUFsbExpc3RlbmVyKHtcblx0XHRcdGJ0bjogYnRuLFxuXHRcdFx0dG9nZ2xlQWxsQnV0dG9uU2VsZWN0b3I6ICcuY29sdW1uLXNvdXJjZXNfd2l0aF9pbnZhbGlkX291dHB1dCBidXR0b24uZXJyb3ItZGV0YWlscy10b2dnbGUnLFxuXHRcdFx0dGFyZ2V0RGV0YWlsc1NlbGVjdG9yOiAnZGV0YWlscy5zb3VyY2UnXG5cdFx0fSk7XG5cdH0pO1xuXG5cdGFkZFRlcm1MaXN0VGFibGVSb3dDbGFzc2VzKCk7XG59KTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Fzc2V0cy9zcmMvYW1wLXZhbGlkYXRpb24tZGV0YWlsLXRvZ2dsZS5qc1xuLy8gbW9kdWxlIGlkID0gNzZcbi8vIG1vZHVsZSBjaHVua3MgPSAyIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///76\n");

/***/ }),

/***/ 77:
/***/ (function(module, exports) {

module.exports = ampValidationI18n;

/***/ })

/******/ });