/**
 * WordPress dependencies
 */
const jsdocConfig = require('@wordpress/eslint-plugin/configs/jsdoc');

// Extend jsdoc defined types.
jsdocConfig.rules['jsdoc/no-undefined-types'][1].definedTypes.push('Backbone');

module.exports = {
	root: true,
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended',
		'plugin:import/recommended',
		'plugin:eslint-comments/recommended',
	],
	env: {
		browser: true,
	},
	rules: {
		'block-scoped-var': 'error',
		complexity: ['error', { max: 20 }],
		'consistent-return': 'error',
		'default-case': 'error',
		'guard-for-in': 'error',
		'no-await-in-loop': 'error',
		'no-extra-bind': 'error',
		'no-extra-label': 'error',
		'no-floating-decimal': 'error',
		'no-implicit-coercion': 'error',
		'no-implicit-globals': 'error',
		'no-implied-eval': 'error',
		'no-loop-func': 'error',
		'no-new': 'error',
		'no-new-func': 'error',
		'no-new-wrappers': 'error',
		'no-restricted-properties': 'error',
		'no-return-assign': 'error',
		'no-return-await': 'error',
		'no-sequences': 'error',
		'no-shadow': 'error',
		'no-template-curly-in-string': 'error',
		'no-throw-literal': 'error',
		'no-unmodified-loop-condition': 'error',
		'no-unused-vars': [
			'error',
			{
				ignoreRestSiblings: true,
			},
		],
		'no-useless-call': 'error',
		'no-useless-concat': 'error',
		'prefer-object-spread': 'error',
		'prefer-promise-reject-errors': 'error',
		'prefer-rest-params': 'error',
		'prefer-spread': 'error',
		radix: ['error', 'as-needed'],
		'require-await': 'error',
		'rest-spread-spacing': ['error', 'never'],
		'react/prop-types': 'error',
		'react-hooks/exhaustive-deps': [
			'error',
			{ additionalHooks: 'useSelect' },
		],
		'react/jsx-closing-tag-location': 'error',
		'react/jsx-fragments': 'error',
		'react/jsx-first-prop-new-line': 'error',
		'react/jsx-max-props-per-line': ['error', { when: 'multiline' }],
		'react/jsx-no-literals': 'error',
		'react/jsx-no-useless-fragment': 'error',
		'react/no-unused-prop-types': 'error',
		'react/self-closing-comp': 'error',
		'import/no-unresolved': [
			'error',
			{
				ignore: [
					'jquery',
					'amp-block-editor-data',
					'amp-settings',
					'amp-themes',
					'amp-plugins',
					'amp-support',
					'amp-block-validation',
					'amp-site-scan-notice',
				],
			},
		],
		'import/order': [
			'error',
			{
				groups: [
					'builtin',
					['external', 'unknown'],
					'internal',
					'parent',
					'sibling',
					'index',
				],
			},
		],
		'jsdoc/check-indentation': 'error',
		'jsdoc/no-undefined-types':
			jsdocConfig.rules['jsdoc/no-undefined-types'],
		'@wordpress/dependency-group': 'error',
		'@wordpress/react-no-unsafe-timeout': 'error',
	},
	overrides: [
		{
			files: [
				'**/__tests__/**/*.js',
				'**/test/*.js',
				'**/?(*.)test.js',
				'tests/js/**/*.js',
			],
			extends: ['plugin:jest/all'],
			rules: {
				'jest/prefer-lowercase-title': [
					'error',
					{
						ignore: ['describe'],
					},
				],
				'jest/max-expects': 'off',
				'jest/no-hooks': 'off',
				'jest/prefer-expect-assertions': 'off',
				'jest/prefer-inline-snapshots': 'off',
				'jest/prefer-snapshot-hint': 'off',
				'jest/no-untyped-mock-factory': 'off',
				// Disabling because we are not using typescript.
				// @see <https://github.com/jest-community/eslint-plugin-jest/blob/main/docs/rules/unbound-method.md>
				'jest/unbound-method': 'off',
			},
		},
		{
			files: ['tests/e2e/**/*.js'],
			extends: [
				'plugin:@wordpress/eslint-plugin/test-e2e',
				'plugin:jest/all',
			],
			rules: {
				'jest/prefer-lowercase-title': [
					'error',
					{
						ignore: ['describe'],
					},
				],
				'jest/max-expects': 'off',
				'jest/no-hooks': 'off',
				'jest/prefer-expect-assertions': 'off',
				'jest/prefer-inline-snapshots': 'off',
				'jest/unbound-method': 'off',
				// Disabling because it's not the Jest environment.
				'jest/prefer-importing-jest-globals': 'off',
			},
		},
		{
			files: ['assets/src/mobile-redirection.js'],
			globals: {
				AMP_MOBILE_REDIRECTION: false,
				location: false,
				navigator: false,
				sessionStorage: false,
			},
		},
		{
			files: ['assets/src/customizer/amp-customize-controls.js'],
			globals: {
				HTMLAnchorElement: false,
			},
		},
	],
};
