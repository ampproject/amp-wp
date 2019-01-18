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
/******/ 	return __webpack_require__(__webpack_require__.s = 75);
/******/ })
/************************************************************************/
/******/ ({

/***/ 75:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/**\n * WordPress dependencies\n */\nvar __ = wp.i18n.__;\nvar _wp$components = wp.components,\n    FormToggle = _wp$components.FormToggle,\n    Notice = _wp$components.Notice;\nvar _wp$element = wp.element,\n    Fragment = _wp$element.Fragment,\n    RawHTML = _wp$element.RawHTML;\nvar _wp$data = wp.data,\n    withSelect = _wp$data.withSelect,\n    withDispatch = _wp$data.withDispatch;\nvar PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;\nvar _wp$compose = wp.compose,\n    compose = _wp$compose.compose,\n    withInstanceId = _wp$compose.withInstanceId;\n\n/**\n * Exported via wp_localize_script().\n */\n\nvar _window$wpAmpEditor = window.wpAmpEditor,\n    possibleStati = _window$wpAmpEditor.possibleStati,\n    defaultStatus = _window$wpAmpEditor.defaultStatus,\n    errorMessages = _window$wpAmpEditor.errorMessages;\n\n/**\n * Adds an 'Enable AMP' toggle to the block editor 'Status & Visibility' section.\n *\n * If there are error(s) that block AMP from being enabled or disabled,\n * this only displays a Notice with the error(s), not a toggle.\n * Error(s) are imported as errorMessages via wp_localize_script().\n *\n * @return {Object} AMPToggle component.\n */\n\nfunction AMPToggle(_ref) {\n\tvar enabledStatus = _ref.enabledStatus,\n\t    onAmpChange = _ref.onAmpChange;\n\n\treturn wp.element.createElement(\n\t\tFragment,\n\t\tnull,\n\t\twp.element.createElement(\n\t\t\tPluginPostStatusInfo,\n\t\t\tnull,\n\t\t\t!errorMessages.length && wp.element.createElement(\n\t\t\t\t'label',\n\t\t\t\t{ htmlFor: 'amp-enabled' },\n\t\t\t\t__('Enable AMP', 'amp')\n\t\t\t),\n\t\t\t!errorMessages.length && wp.element.createElement(FormToggle, {\n\t\t\t\tchecked: 'enabled' === enabledStatus,\n\t\t\t\tonChange: function onChange() {\n\t\t\t\t\treturn onAmpChange(enabledStatus);\n\t\t\t\t},\n\t\t\t\tid: 'amp-enabled'\n\t\t\t}),\n\t\t\t!!errorMessages.length && wp.element.createElement(\n\t\t\t\tNotice,\n\t\t\t\t{\n\t\t\t\t\tstatus: 'warning',\n\t\t\t\t\tisDismissible: false\n\t\t\t\t},\n\t\t\t\terrorMessages.map(function (message, index) {\n\t\t\t\t\treturn wp.element.createElement(\n\t\t\t\t\t\tRawHTML,\n\t\t\t\t\t\t{ key: index },\n\t\t\t\t\t\tmessage\n\t\t\t\t\t);\n\t\t\t\t})\n\t\t\t)\n\t\t)\n\t);\n}\n\n/**\n * The AMP Toggle component, composed with the enabledStatus and a callback for when it's changed.\n *\n * @return {Object} The composed AMP toggle.\n */\nfunction ComposedAMPToggle() {\n\treturn compose([withSelect(function (select) {\n\t\t/**\n   * Gets the AMP enabled status.\n   *\n   * Uses select from the enclosing function to get the meta value.\n   * If it doesn't exist, it uses the default value.\n   * This applies especially for a new post, where there probably won't be a meta value yet.\n   *\n   * @return {string} Enabled status, either 'enabled' or 'disabled'.\n   */\n\t\tvar getEnabledStatus = function getEnabledStatus() {\n\t\t\tvar meta = select('core/editor').getEditedPostAttribute('meta');\n\t\t\tif (meta && meta.amp_status && possibleStati.includes(meta.amp_status)) {\n\t\t\t\treturn meta.amp_status;\n\t\t\t}\n\t\t\treturn defaultStatus;\n\t\t};\n\n\t\treturn { enabledStatus: getEnabledStatus() };\n\t}), withDispatch(function (dispatch) {\n\t\treturn {\n\t\t\tonAmpChange: function onAmpChange(enabledStatus) {\n\t\t\t\tvar newStatus = 'enabled' === enabledStatus ? 'disabled' : 'enabled';\n\t\t\t\tdispatch('core/editor').editPost({ meta: { amp_status: newStatus } });\n\t\t\t}\n\t\t};\n\t}), withInstanceId])(AMPToggle);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (wp.plugins.registerPlugin('amp', {\n\ticon: 'hidden',\n\trender: ComposedAMPToggle()\n}));//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiNzUuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2FtcC1ibG9jay1lZGl0b3ItdG9nZ2xlLmpzPzVkMDkiXSwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBXb3JkUHJlc3MgZGVwZW5kZW5jaWVzXG4gKi9cbnZhciBfXyA9IHdwLmkxOG4uX187XG52YXIgX3dwJGNvbXBvbmVudHMgPSB3cC5jb21wb25lbnRzLFxuICAgIEZvcm1Ub2dnbGUgPSBfd3AkY29tcG9uZW50cy5Gb3JtVG9nZ2xlLFxuICAgIE5vdGljZSA9IF93cCRjb21wb25lbnRzLk5vdGljZTtcbnZhciBfd3AkZWxlbWVudCA9IHdwLmVsZW1lbnQsXG4gICAgRnJhZ21lbnQgPSBfd3AkZWxlbWVudC5GcmFnbWVudCxcbiAgICBSYXdIVE1MID0gX3dwJGVsZW1lbnQuUmF3SFRNTDtcbnZhciBfd3AkZGF0YSA9IHdwLmRhdGEsXG4gICAgd2l0aFNlbGVjdCA9IF93cCRkYXRhLndpdGhTZWxlY3QsXG4gICAgd2l0aERpc3BhdGNoID0gX3dwJGRhdGEud2l0aERpc3BhdGNoO1xudmFyIFBsdWdpblBvc3RTdGF0dXNJbmZvID0gd3AuZWRpdFBvc3QuUGx1Z2luUG9zdFN0YXR1c0luZm87XG52YXIgX3dwJGNvbXBvc2UgPSB3cC5jb21wb3NlLFxuICAgIGNvbXBvc2UgPSBfd3AkY29tcG9zZS5jb21wb3NlLFxuICAgIHdpdGhJbnN0YW5jZUlkID0gX3dwJGNvbXBvc2Uud2l0aEluc3RhbmNlSWQ7XG5cbi8qKlxuICogRXhwb3J0ZWQgdmlhIHdwX2xvY2FsaXplX3NjcmlwdCgpLlxuICovXG5cbnZhciBfd2luZG93JHdwQW1wRWRpdG9yID0gd2luZG93LndwQW1wRWRpdG9yLFxuICAgIHBvc3NpYmxlU3RhdGkgPSBfd2luZG93JHdwQW1wRWRpdG9yLnBvc3NpYmxlU3RhdGksXG4gICAgZGVmYXVsdFN0YXR1cyA9IF93aW5kb3ckd3BBbXBFZGl0b3IuZGVmYXVsdFN0YXR1cyxcbiAgICBlcnJvck1lc3NhZ2VzID0gX3dpbmRvdyR3cEFtcEVkaXRvci5lcnJvck1lc3NhZ2VzO1xuXG4vKipcbiAqIEFkZHMgYW4gJ0VuYWJsZSBBTVAnIHRvZ2dsZSB0byB0aGUgYmxvY2sgZWRpdG9yICdTdGF0dXMgJiBWaXNpYmlsaXR5JyBzZWN0aW9uLlxuICpcbiAqIElmIHRoZXJlIGFyZSBlcnJvcihzKSB0aGF0IGJsb2NrIEFNUCBmcm9tIGJlaW5nIGVuYWJsZWQgb3IgZGlzYWJsZWQsXG4gKiB0aGlzIG9ubHkgZGlzcGxheXMgYSBOb3RpY2Ugd2l0aCB0aGUgZXJyb3IocyksIG5vdCBhIHRvZ2dsZS5cbiAqIEVycm9yKHMpIGFyZSBpbXBvcnRlZCBhcyBlcnJvck1lc3NhZ2VzIHZpYSB3cF9sb2NhbGl6ZV9zY3JpcHQoKS5cbiAqXG4gKiBAcmV0dXJuIHtPYmplY3R9IEFNUFRvZ2dsZSBjb21wb25lbnQuXG4gKi9cblxuZnVuY3Rpb24gQU1QVG9nZ2xlKF9yZWYpIHtcblx0dmFyIGVuYWJsZWRTdGF0dXMgPSBfcmVmLmVuYWJsZWRTdGF0dXMsXG5cdCAgICBvbkFtcENoYW5nZSA9IF9yZWYub25BbXBDaGFuZ2U7XG5cblx0cmV0dXJuIHdwLmVsZW1lbnQuY3JlYXRlRWxlbWVudChcblx0XHRGcmFnbWVudCxcblx0XHRudWxsLFxuXHRcdHdwLmVsZW1lbnQuY3JlYXRlRWxlbWVudChcblx0XHRcdFBsdWdpblBvc3RTdGF0dXNJbmZvLFxuXHRcdFx0bnVsbCxcblx0XHRcdCFlcnJvck1lc3NhZ2VzLmxlbmd0aCAmJiB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoXG5cdFx0XHRcdCdsYWJlbCcsXG5cdFx0XHRcdHsgaHRtbEZvcjogJ2FtcC1lbmFibGVkJyB9LFxuXHRcdFx0XHRfXygnRW5hYmxlIEFNUCcsICdhbXAnKVxuXHRcdFx0KSxcblx0XHRcdCFlcnJvck1lc3NhZ2VzLmxlbmd0aCAmJiB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoRm9ybVRvZ2dsZSwge1xuXHRcdFx0XHRjaGVja2VkOiAnZW5hYmxlZCcgPT09IGVuYWJsZWRTdGF0dXMsXG5cdFx0XHRcdG9uQ2hhbmdlOiBmdW5jdGlvbiBvbkNoYW5nZSgpIHtcblx0XHRcdFx0XHRyZXR1cm4gb25BbXBDaGFuZ2UoZW5hYmxlZFN0YXR1cyk7XG5cdFx0XHRcdH0sXG5cdFx0XHRcdGlkOiAnYW1wLWVuYWJsZWQnXG5cdFx0XHR9KSxcblx0XHRcdCEhZXJyb3JNZXNzYWdlcy5sZW5ndGggJiYgd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KFxuXHRcdFx0XHROb3RpY2UsXG5cdFx0XHRcdHtcblx0XHRcdFx0XHRzdGF0dXM6ICd3YXJuaW5nJyxcblx0XHRcdFx0XHRpc0Rpc21pc3NpYmxlOiBmYWxzZVxuXHRcdFx0XHR9LFxuXHRcdFx0XHRlcnJvck1lc3NhZ2VzLm1hcChmdW5jdGlvbiAobWVzc2FnZSwgaW5kZXgpIHtcblx0XHRcdFx0XHRyZXR1cm4gd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KFxuXHRcdFx0XHRcdFx0UmF3SFRNTCxcblx0XHRcdFx0XHRcdHsga2V5OiBpbmRleCB9LFxuXHRcdFx0XHRcdFx0bWVzc2FnZVxuXHRcdFx0XHRcdCk7XG5cdFx0XHRcdH0pXG5cdFx0XHQpXG5cdFx0KVxuXHQpO1xufVxuXG4vKipcbiAqIFRoZSBBTVAgVG9nZ2xlIGNvbXBvbmVudCwgY29tcG9zZWQgd2l0aCB0aGUgZW5hYmxlZFN0YXR1cyBhbmQgYSBjYWxsYmFjayBmb3Igd2hlbiBpdCdzIGNoYW5nZWQuXG4gKlxuICogQHJldHVybiB7T2JqZWN0fSBUaGUgY29tcG9zZWQgQU1QIHRvZ2dsZS5cbiAqL1xuZnVuY3Rpb24gQ29tcG9zZWRBTVBUb2dnbGUoKSB7XG5cdHJldHVybiBjb21wb3NlKFt3aXRoU2VsZWN0KGZ1bmN0aW9uIChzZWxlY3QpIHtcblx0XHQvKipcbiAgICogR2V0cyB0aGUgQU1QIGVuYWJsZWQgc3RhdHVzLlxuICAgKlxuICAgKiBVc2VzIHNlbGVjdCBmcm9tIHRoZSBlbmNsb3NpbmcgZnVuY3Rpb24gdG8gZ2V0IHRoZSBtZXRhIHZhbHVlLlxuICAgKiBJZiBpdCBkb2Vzbid0IGV4aXN0LCBpdCB1c2VzIHRoZSBkZWZhdWx0IHZhbHVlLlxuICAgKiBUaGlzIGFwcGxpZXMgZXNwZWNpYWxseSBmb3IgYSBuZXcgcG9zdCwgd2hlcmUgdGhlcmUgcHJvYmFibHkgd29uJ3QgYmUgYSBtZXRhIHZhbHVlIHlldC5cbiAgICpcbiAgICogQHJldHVybiB7c3RyaW5nfSBFbmFibGVkIHN0YXR1cywgZWl0aGVyICdlbmFibGVkJyBvciAnZGlzYWJsZWQnLlxuICAgKi9cblx0XHR2YXIgZ2V0RW5hYmxlZFN0YXR1cyA9IGZ1bmN0aW9uIGdldEVuYWJsZWRTdGF0dXMoKSB7XG5cdFx0XHR2YXIgbWV0YSA9IHNlbGVjdCgnY29yZS9lZGl0b3InKS5nZXRFZGl0ZWRQb3N0QXR0cmlidXRlKCdtZXRhJyk7XG5cdFx0XHRpZiAobWV0YSAmJiBtZXRhLmFtcF9zdGF0dXMgJiYgcG9zc2libGVTdGF0aS5pbmNsdWRlcyhtZXRhLmFtcF9zdGF0dXMpKSB7XG5cdFx0XHRcdHJldHVybiBtZXRhLmFtcF9zdGF0dXM7XG5cdFx0XHR9XG5cdFx0XHRyZXR1cm4gZGVmYXVsdFN0YXR1cztcblx0XHR9O1xuXG5cdFx0cmV0dXJuIHsgZW5hYmxlZFN0YXR1czogZ2V0RW5hYmxlZFN0YXR1cygpIH07XG5cdH0pLCB3aXRoRGlzcGF0Y2goZnVuY3Rpb24gKGRpc3BhdGNoKSB7XG5cdFx0cmV0dXJuIHtcblx0XHRcdG9uQW1wQ2hhbmdlOiBmdW5jdGlvbiBvbkFtcENoYW5nZShlbmFibGVkU3RhdHVzKSB7XG5cdFx0XHRcdHZhciBuZXdTdGF0dXMgPSAnZW5hYmxlZCcgPT09IGVuYWJsZWRTdGF0dXMgPyAnZGlzYWJsZWQnIDogJ2VuYWJsZWQnO1xuXHRcdFx0XHRkaXNwYXRjaCgnY29yZS9lZGl0b3InKS5lZGl0UG9zdCh7IG1ldGE6IHsgYW1wX3N0YXR1czogbmV3U3RhdHVzIH0gfSk7XG5cdFx0XHR9XG5cdFx0fTtcblx0fSksIHdpdGhJbnN0YW5jZUlkXSkoQU1QVG9nZ2xlKTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgd3AucGx1Z2lucy5yZWdpc3RlclBsdWdpbignYW1wJywge1xuXHRpY29uOiAnaGlkZGVuJyxcblx0cmVuZGVyOiBDb21wb3NlZEFNUFRvZ2dsZSgpXG59KTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL2Fzc2V0cy9zcmMvYW1wLWJsb2NrLWVkaXRvci10b2dnbGUuanNcbi8vIG1vZHVsZSBpZCA9IDc1XG4vLyBtb2R1bGUgY2h1bmtzID0gNSJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///75\n");

/***/ })

/******/ });