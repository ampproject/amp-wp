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
/******/ 	return __webpack_require__(__webpack_require__.s = 16);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("/**\n * Specify a function to execute when the DOM is fully loaded.\n *\n * @param {Function} callback A function to execute after the DOM is ready.\n *\n * @return {void}\n */\nvar domReady = function domReady(callback) {\n  if (document.readyState === 'complete' || // DOMContentLoaded + Images/Styles/etc loaded, so we call directly.\n  document.readyState === 'interactive' // DOMContentLoaded fires at this point, so we call directly.\n  ) {\n      return callback();\n    } // DOMContentLoaded has not fired yet, delay callback until then.\n\n\n  document.addEventListener('DOMContentLoaded', callback);\n};\n\n/* harmony default export */ __webpack_exports__[\"a\"] = (domReady);\n//# sourceMappingURL=index.js.map//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMS5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9Ad29yZHByZXNzL2RvbS1yZWFkeS9idWlsZC1tb2R1bGUvaW5kZXguanM/YTc2MyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFNwZWNpZnkgYSBmdW5jdGlvbiB0byBleGVjdXRlIHdoZW4gdGhlIERPTSBpcyBmdWxseSBsb2FkZWQuXG4gKlxuICogQHBhcmFtIHtGdW5jdGlvbn0gY2FsbGJhY2sgQSBmdW5jdGlvbiB0byBleGVjdXRlIGFmdGVyIHRoZSBET00gaXMgcmVhZHkuXG4gKlxuICogQHJldHVybiB7dm9pZH1cbiAqL1xudmFyIGRvbVJlYWR5ID0gZnVuY3Rpb24gZG9tUmVhZHkoY2FsbGJhY2spIHtcbiAgaWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdjb21wbGV0ZScgfHwgLy8gRE9NQ29udGVudExvYWRlZCArIEltYWdlcy9TdHlsZXMvZXRjIGxvYWRlZCwgc28gd2UgY2FsbCBkaXJlY3RseS5cbiAgZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2ludGVyYWN0aXZlJyAvLyBET01Db250ZW50TG9hZGVkIGZpcmVzIGF0IHRoaXMgcG9pbnQsIHNvIHdlIGNhbGwgZGlyZWN0bHkuXG4gICkge1xuICAgICAgcmV0dXJuIGNhbGxiYWNrKCk7XG4gICAgfSAvLyBET01Db250ZW50TG9hZGVkIGhhcyBub3QgZmlyZWQgeWV0LCBkZWxheSBjYWxsYmFjayB1bnRpbCB0aGVuLlxuXG5cbiAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIGNhbGxiYWNrKTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGRvbVJlYWR5O1xuLy8jIHNvdXJjZU1hcHBpbmdVUkw9aW5kZXguanMubWFwXG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9ub2RlX21vZHVsZXMvQHdvcmRwcmVzcy9kb20tcmVhZHkvYnVpbGQtbW9kdWxlL2luZGV4LmpzXG4vLyBtb2R1bGUgaWQgPSAxXG4vLyBtb2R1bGUgY2h1bmtzID0gMSAyIDMiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///1\n");

/***/ }),

/***/ 16:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__ = __webpack_require__(1);\n/**\n * WordPress dependencies\n */\n\n\n// WIP Pointer function\nfunction sourcesPointer() {\n\tjQuery(document).on('click', '.tooltip-button', function () {\n\t\tjQuery(this).pointer({\n\t\t\tcontent: jQuery(this).next('.tooltip').attr('data-content'),\n\t\t\tposition: {\n\t\t\t\tedge: 'left',\n\t\t\t\talign: 'center'\n\t\t\t},\n\t\t\tpointerClass: 'wp-pointer wp-pointer--tooltip'\n\t\t}).pointer('open');\n\t});\n}\n\nObject(__WEBPACK_IMPORTED_MODULE_0__wordpress_dom_ready__[\"a\" /* default */])(function () {\n\tsourcesPointer();\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMTYuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLXRvb2x0aXBzLmpzPzAxYTMiXSwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBXb3JkUHJlc3MgZGVwZW5kZW5jaWVzXG4gKi9cbmltcG9ydCBkb21SZWFkeSBmcm9tICdAd29yZHByZXNzL2RvbS1yZWFkeSc7XG5cbi8vIFdJUCBQb2ludGVyIGZ1bmN0aW9uXG5mdW5jdGlvbiBzb3VyY2VzUG9pbnRlcigpIHtcblx0alF1ZXJ5KGRvY3VtZW50KS5vbignY2xpY2snLCAnLnRvb2x0aXAtYnV0dG9uJywgZnVuY3Rpb24gKCkge1xuXHRcdGpRdWVyeSh0aGlzKS5wb2ludGVyKHtcblx0XHRcdGNvbnRlbnQ6IGpRdWVyeSh0aGlzKS5uZXh0KCcudG9vbHRpcCcpLmF0dHIoJ2RhdGEtY29udGVudCcpLFxuXHRcdFx0cG9zaXRpb246IHtcblx0XHRcdFx0ZWRnZTogJ2xlZnQnLFxuXHRcdFx0XHRhbGlnbjogJ2NlbnRlcidcblx0XHRcdH0sXG5cdFx0XHRwb2ludGVyQ2xhc3M6ICd3cC1wb2ludGVyIHdwLXBvaW50ZXItLXRvb2x0aXAnXG5cdFx0fSkucG9pbnRlcignb3BlbicpO1xuXHR9KTtcbn1cblxuZG9tUmVhZHkoZnVuY3Rpb24gKCkge1xuXHRzb3VyY2VzUG9pbnRlcigpO1xufSk7XG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9hc3NldHMvc3JjL2FtcC12YWxpZGF0aW9uLXRvb2x0aXBzLmpzXG4vLyBtb2R1bGUgaWQgPSAxNlxuLy8gbW9kdWxlIGNodW5rcyA9IDIiXSwibWFwcGluZ3MiOiJBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///16\n");

/***/ })

/******/ });