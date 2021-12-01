module.exports = {
	...require( './jest.config' ),
	reporters: [
		[ 'jest-silent-reporter', { useDots: true } ],
		'<rootDir>/../../node_modules/@wordpress/scripts/config/jest-github-actions-reporter',
	],
};
