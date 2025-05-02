module.exports = {
	...require('./jest.config'),
	reporters: [
		['jest-silent-reporter', { useDots: true, showPaths: true }],
		'<rootDir>/../../node_modules/@wordpress/scripts/config/jest-github-actions-reporter',
	],
};
