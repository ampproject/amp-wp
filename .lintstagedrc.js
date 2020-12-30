module.exports = {
	"package.json": [
		"npm run lint:pkg-json"
	],
	"**/*.css": [
		"npm run lint:css"
	],
	"**/*.js": [
		"npm run lint:js"
	],
	"**/!(amp).php": [
		"npm run lint:php"
	],
	"amp.php": [
		"vendor/bin/phpcs --runtime-set testVersion 5.2-"
	],
	"*.php": () => 'composer analyze'
};
