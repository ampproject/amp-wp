module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	moduleNameMapper: {
		'^@wordpress\\/element$': '<rootDir>/tests/shared/test-utils/wp-element-mock',
	},
	setupFiles: [
		'<rootDir>/tests/js/setup-globals',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git',
		'<rootDir>/node_modules',
		'<rootDir>/build',
		'_utils',
	],
	coveragePathIgnorePatterns: [ '/node_modules/', '<rootDir>/build/' ],
	coverageReporters: [ 'lcov' ],
	coverageDirectory: '<rootDir>/build/logs',
	collectCoverage: true,
	collectCoverageFrom: [
		'<rootDir>/assets/src/edit-story/**/*.js',
		'!**/test/**',
	],
	reporters: [ [ 'jest-silent-reporter', { useDots: true } ] ],
};
