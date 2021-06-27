module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFiles: [
		'<rootDir>/tests/js/setup-globals',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git',
		'<rootDir>/node_modules',
		'<rootDir>/build',
		'<rootDir>/tests/shared',
		'<rootDir>/vendor',
		'<rootDir>/assets/src/block-editor/plugins',
		'<rootDir>/assets/src/block-validation/plugins',
	],
	coveragePathIgnorePatterns: [
		'<rootDir>/node_modules',
		'<rootDir>/build/',
		'<rootDir>/tests/shared',
		'<rootDir>/assets/src/block-editor/plugins',
		'<rootDir>/assets/src/block-validation/plugins',
	],
	modulePathIgnorePatterns: [
		'<rootDir>/assets/src/components/.*/__mocks__',
		'<rootDir>/assets/src/components/.*/__data__',
	],
	coverageReporters: [ 'lcov' ],
	coverageDirectory: '<rootDir>/build/logs',
	reporters: [
		[ 'jest-silent-reporter', { useDots: true } ],
		'<rootDir>/node_modules/@wordpress/scripts/config/jest-github-actions-reporter.js',
	],
};
