module.exports = {
	launch: {
		dumpio: true,
		headless: process.env.HEADLESS !== 'false',
	},
	browserContext: 'default',
};
