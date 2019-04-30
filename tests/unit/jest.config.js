module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFiles: [
		'<rootDir>/tests/unit/setup-globals',
	],
	testPathIgnorePatterns: [
		'/\.git/',
		'/node_modules/',
	],
};
