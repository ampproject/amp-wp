export default [
	{
		url: 'https://example.org/',
		type: 'is_front_page',
		label: 'Homepage',
		amp_url: 'https://example.org/?amp=1',
		validation_errors: [
			{
				code: 'CSS_SYNTAX_INVALID_PROPERTY_NOLIST',
				css_property_name: 'behavior',
				css_property_value: "url('/bad.htc')",
				type: 'css_error',
				spec_name: 'style amp-custom',
				node_name: 'link',
				parent_name: 'head',
				node_attributes: {
					rel: 'stylesheet',
					id: 'bad-block-front-block-style-handle-css',
					href: 'https://example.org/wp-content/plugins/bad-block/front-block-style-handle.css?ver=__normalized__',
					media: 'all',
				},
				node_type: 1,
				sources: [
					{
						type: 'plugin',
						name: 'bad-block',
						file: 'bad-block.php',
						line: 42,
						function: 'Bad\\Block\\bad_block_init',
						hook: 'init',
						priority: 10,
						dependency_type: 'style',
						handle: 'bad-block-front-block-style-handle',
					},
					{
						type: 'core',
						name: 'wp-includes',
						file: 'script-loader.php',
						line: 2223,
						function:
							'wp_enqueue_registered_block_scripts_and_styles',
						hook: 'enqueue_block_assets',
						priority: 10,
						dependency_type: 'style',
						handle: 'bad-block-front-block-style-handle',
					},
				],
			},
			{
				node_name: 'script',
				parent_name: 'head',
				code: 'DISALLOWED_TAG',
				type: 'js_error',
				node_attributes: {
					src: 'https://example.org/wp-content/plugins/bad-block/front-block-script-handle.js?ver=__normalized__',
					id: 'bad-block-front-block-script-handle-js',
				},
				node_type: 1,
				sources: [
					{
						type: 'core',
						name: 'wp-includes',
						file: 'script-loader.php',
						line: 1952,
						function: 'wp_print_head_scripts',
						hook: 'wp_head',
						priority: 9,
					},
				],
			},
		],
		validated_url_post: {
			id: 15350,
			edit_link:
				'https://example.org/wp-admin/post.php?post=15350&action=edit',
		},
		stale: false,
	},
];
