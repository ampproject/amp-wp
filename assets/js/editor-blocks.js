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
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__amp_timeout__ = __webpack_require__(1);\n/**\n * Import blocks.\n */\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Jsb2Nrcy9pbmRleC5qcz84MTkzIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogSW1wb3J0IGJsb2Nrcy5cbiAqL1xuaW1wb3J0ICcuL2FtcC10aW1lb3V0JztcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Jsb2Nrcy9pbmRleC5qc1xuLy8gbW9kdWxlIGlkID0gMFxuLy8gbW9kdWxlIGNodW5rcyA9IDAiXSwibWFwcGluZ3MiOiJBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7Iiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///0\n");

/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("/**\n * Internal block libraries.\n */\nvar __ = wp.i18n.__;\nvar _wp$blocks = wp.blocks,\n    registerBlockType = _wp$blocks.registerBlockType,\n    InspectorControls = _wp$blocks.InspectorControls;\nvar _wp$components = wp.components,\n    DateTimePicker = _wp$components.DateTimePicker,\n    PanelBody = _wp$components.PanelBody;\n\n/**\n * Register block.\n */\n\n/* unused harmony default export */ var _unused_webpack_default_export = (registerBlockType('amp/amp-timeago', {\n\ttitle: __('AMP Timeago'),\n\tcategory: 'common',\n\ticon: 'wordpress-alt',\n\tkeywords: [__('Time difference'), __('Time ago'), __('Date')],\n\n\tattributes: {\n\t\tdateTime: {\n\t\t\tsource: 'children',\n\t\t\ttype: 'array',\n\t\t\tselector: 'amp-timeago'\n\t\t}\n\t},\n\n\tedit: function edit(_ref) {\n\t\tvar attributes = _ref.attributes,\n\t\t    isSelected = _ref.isSelected,\n\t\t    setAttributes = _ref.setAttributes;\n\n\t\tvar timeMoment, timeAgo;\n\t\tif (attributes.dateTime) {\n\t\t\ttimeMoment = moment(attributes.dateTime);\n\t\t\ttimeAgo = timeMoment.fromNow();\n\t\t} else {\n\t\t\ttimeAgo = moment().fromNow();\n\t\t}\n\t\treturn [isSelected && wp.element.createElement(\n\t\t\tInspectorControls,\n\t\t\t{ key: 'inspector' },\n\t\t\twp.element.createElement(\n\t\t\t\tPanelBody,\n\t\t\t\t{ title: __('AMP Timeago Settings') },\n\t\t\t\twp.element.createElement(DateTimePicker, {\n\t\t\t\t\tlocale: 'en',\n\t\t\t\t\tcurrentDate: attributes.dateTime || moment(),\n\t\t\t\t\tonChange: function onChange(value) {\n\t\t\t\t\t\treturn setAttributes({ dateTime: value });\n\t\t\t\t\t} // eslint-disable-line\n\t\t\t\t})\n\t\t\t)\n\t\t), wp.element.createElement(\n\t\t\t'time',\n\t\t\t{ dateTime: '2017-04-11T00:37:33.809Z' },\n\t\t\ttimeAgo\n\t\t)];\n\t},\n\tsave: function save(_ref2) {\n\t\tvar attributes = _ref2.attributes;\n\n\t\treturn wp.element.createElement(\n\t\t\t'amp-timeago',\n\t\t\t{ layout: 'fixed', width: '160',\n\t\t\t\theight: '20',\n\t\t\t\tdateTime: attributes.dateTime,\n\t\t\t\tlocale: 'en' },\n\t\t\tattributes.dateTime\n\t\t);\n\t}\n}));//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMS5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Jsb2Nrcy9hbXAtdGltZW91dC9pbmRleC5qcz84YzdhIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogSW50ZXJuYWwgYmxvY2sgbGlicmFyaWVzLlxuICovXG52YXIgX18gPSB3cC5pMThuLl9fO1xudmFyIF93cCRibG9ja3MgPSB3cC5ibG9ja3MsXG4gICAgcmVnaXN0ZXJCbG9ja1R5cGUgPSBfd3AkYmxvY2tzLnJlZ2lzdGVyQmxvY2tUeXBlLFxuICAgIEluc3BlY3RvckNvbnRyb2xzID0gX3dwJGJsb2Nrcy5JbnNwZWN0b3JDb250cm9scztcbnZhciBfd3AkY29tcG9uZW50cyA9IHdwLmNvbXBvbmVudHMsXG4gICAgRGF0ZVRpbWVQaWNrZXIgPSBfd3AkY29tcG9uZW50cy5EYXRlVGltZVBpY2tlcixcbiAgICBQYW5lbEJvZHkgPSBfd3AkY29tcG9uZW50cy5QYW5lbEJvZHk7XG5cbi8qKlxuICogUmVnaXN0ZXIgYmxvY2suXG4gKi9cblxuZXhwb3J0IGRlZmF1bHQgcmVnaXN0ZXJCbG9ja1R5cGUoJ2FtcC9hbXAtdGltZWFnbycsIHtcblx0dGl0bGU6IF9fKCdBTVAgVGltZWFnbycpLFxuXHRjYXRlZ29yeTogJ2NvbW1vbicsXG5cdGljb246ICd3b3JkcHJlc3MtYWx0Jyxcblx0a2V5d29yZHM6IFtfXygnVGltZSBkaWZmZXJlbmNlJyksIF9fKCdUaW1lIGFnbycpLCBfXygnRGF0ZScpXSxcblxuXHRhdHRyaWJ1dGVzOiB7XG5cdFx0ZGF0ZVRpbWU6IHtcblx0XHRcdHNvdXJjZTogJ2NoaWxkcmVuJyxcblx0XHRcdHR5cGU6ICdhcnJheScsXG5cdFx0XHRzZWxlY3RvcjogJ2FtcC10aW1lYWdvJ1xuXHRcdH1cblx0fSxcblxuXHRlZGl0OiBmdW5jdGlvbiBlZGl0KF9yZWYpIHtcblx0XHR2YXIgYXR0cmlidXRlcyA9IF9yZWYuYXR0cmlidXRlcyxcblx0XHQgICAgaXNTZWxlY3RlZCA9IF9yZWYuaXNTZWxlY3RlZCxcblx0XHQgICAgc2V0QXR0cmlidXRlcyA9IF9yZWYuc2V0QXR0cmlidXRlcztcblxuXHRcdHZhciB0aW1lTW9tZW50LCB0aW1lQWdvO1xuXHRcdGlmIChhdHRyaWJ1dGVzLmRhdGVUaW1lKSB7XG5cdFx0XHR0aW1lTW9tZW50ID0gbW9tZW50KGF0dHJpYnV0ZXMuZGF0ZVRpbWUpO1xuXHRcdFx0dGltZUFnbyA9IHRpbWVNb21lbnQuZnJvbU5vdygpO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHR0aW1lQWdvID0gbW9tZW50KCkuZnJvbU5vdygpO1xuXHRcdH1cblx0XHRyZXR1cm4gW2lzU2VsZWN0ZWQgJiYgd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KFxuXHRcdFx0SW5zcGVjdG9yQ29udHJvbHMsXG5cdFx0XHR7IGtleTogJ2luc3BlY3RvcicgfSxcblx0XHRcdHdwLmVsZW1lbnQuY3JlYXRlRWxlbWVudChcblx0XHRcdFx0UGFuZWxCb2R5LFxuXHRcdFx0XHR7IHRpdGxlOiBfXygnQU1QIFRpbWVhZ28gU2V0dGluZ3MnKSB9LFxuXHRcdFx0XHR3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoRGF0ZVRpbWVQaWNrZXIsIHtcblx0XHRcdFx0XHRsb2NhbGU6ICdlbicsXG5cdFx0XHRcdFx0Y3VycmVudERhdGU6IGF0dHJpYnV0ZXMuZGF0ZVRpbWUgfHwgbW9tZW50KCksXG5cdFx0XHRcdFx0b25DaGFuZ2U6IGZ1bmN0aW9uIG9uQ2hhbmdlKHZhbHVlKSB7XG5cdFx0XHRcdFx0XHRyZXR1cm4gc2V0QXR0cmlidXRlcyh7IGRhdGVUaW1lOiB2YWx1ZSB9KTtcblx0XHRcdFx0XHR9IC8vIGVzbGludC1kaXNhYmxlLWxpbmVcblx0XHRcdFx0fSlcblx0XHRcdClcblx0XHQpLCB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoXG5cdFx0XHQndGltZScsXG5cdFx0XHR7IGRhdGVUaW1lOiAnMjAxNy0wNC0xMVQwMDozNzozMy44MDlaJyB9LFxuXHRcdFx0dGltZUFnb1xuXHRcdCldO1xuXHR9LFxuXHRzYXZlOiBmdW5jdGlvbiBzYXZlKF9yZWYyKSB7XG5cdFx0dmFyIGF0dHJpYnV0ZXMgPSBfcmVmMi5hdHRyaWJ1dGVzO1xuXG5cdFx0cmV0dXJuIHdwLmVsZW1lbnQuY3JlYXRlRWxlbWVudChcblx0XHRcdCdhbXAtdGltZWFnbycsXG5cdFx0XHR7IGxheW91dDogJ2ZpeGVkJywgd2lkdGg6ICcxNjAnLFxuXHRcdFx0XHRoZWlnaHQ6ICcyMCcsXG5cdFx0XHRcdGRhdGVUaW1lOiBhdHRyaWJ1dGVzLmRhdGVUaW1lLFxuXHRcdFx0XHRsb2NhbGU6ICdlbicgfSxcblx0XHRcdGF0dHJpYnV0ZXMuZGF0ZVRpbWVcblx0XHQpO1xuXHR9XG59KTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Jsb2Nrcy9hbXAtdGltZW91dC9pbmRleC5qc1xuLy8gbW9kdWxlIGlkID0gMVxuLy8gbW9kdWxlIGNodW5rcyA9IDAiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///1\n");

/***/ })
/******/ ]);