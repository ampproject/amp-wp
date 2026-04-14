module.exports = {
	'package.json': ['wp-scripts lint-pkg-json'],
	'**/*.(css|scss)': ['npm run lint:css'],
	'**/*.js': ['eslint'],
	'**/!(amp.php).php': ['npm run lint:php'],
	'amp.php': ['vendor/bin/phpcs --runtime-set testVersion 5.2-'],
	'*.php': () => 'composer analyze',
};
