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

global.ajaxurl = 'http://site.test/wp-admin/ajax.php';

global.CSS = {};
