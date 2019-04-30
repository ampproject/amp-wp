// Set up `wp.*` aliases.
global.wp = {
	media: {
		controller: {
			Library: jest.fn(),
			Cropper: {
				extend: jest.fn(),
			},
		},
		view: {
			extend: jest.fn(),
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
