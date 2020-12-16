module.exports = {
	blockSources: {
		'my-plugin/test-block': {
			source: 'plugin',
			title: 'My plugin',
		},
		'my-mu-plugin/test-block': {
			source: 'mu-plugin',
			title: 'My MU plugin',
		},
		'my-theme/test-block': {
			source: 'theme',
			title: 'My theme',
		},
		'core/test-block': {
			source: '',
			title: 'WordPress core',
		},
		'unknown/test-block': {
			source: '',
			name: '',
		},
	},
	CSS_ERROR_TYPE: 'css_error',
	HTML_ATTRIBUTE_ERROR_TYPE: 'html_attribute_error',
	HTML_ELEMENT_ERROR_TYPE: 'html_element_error',
	JS_ERROR_TYPE: 'js_error',
	pluginNames: {
		'test-plugin': 'Test plugin',
		'test-mu-plugin': 'Test MU plugin',
		'test-plugin-2': 'Test plugin 2',
		'test-mu-plugin-2': 'Test MU plugin 2',
	},
	themeName: 'Test theme',
	themeSlug: 'test-theme',
};
