// Set up `wp.*` aliases.
global.wp = {
	media: {
		controller: {
			Library: jest.fn(),
			Cropper: {
				extend: jest.fn(),
			},
		},
		View: {
			extend: jest.fn(),
		},
		view: {
			Toolbar: {
				Select: {
					extend: jest.fn(),
				},
			},
			MediaFrame: {
				Select: {
					extend: jest.fn(),
				},
			},
		},
	},
};

global.ampStoriesFonts = [
	{
		name: 'Arial',
		slug: 'arial',
	},
	{
		name: 'Roboto',
		slug: 'roboto',
		handle: 'roboto-font',
		src: 'https://fonts.googleapis.com/css?family=Roboto%3A400%2C700&subset=latin%2Clatin-ext',
	},
	{
		name: 'Ubuntu',
		slug: 'ubuntu',
		src: 'https://fonts.googleapis.com/css?family=Ubuntu%3A400%2C700&subset=latin%2Clatin-ext',
	},
	{
		name: 'Verdana',
		slug: 'verdana',
		handle: 'verdana-font',
	},
];
