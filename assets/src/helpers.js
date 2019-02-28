import { __ } from '@wordpress/i18n';

export const ALLOWED_BLOCKS = [
	'core/audio',
	'core/code',
	'core/embed',
	'core/image',
	'core/list',
	'core/preformatted',
	'core/pullquote',
	'core/quote',
	'core/table',
	'core/verse',
	'core/video',
	'amp/amp-story-text',
];

export const BLOCK_ICONS = {
	'amp/amp-story-page': <svg id="story-page-icon" viewBox="0 0 24 24"><g id="icon" fill="#181D21"><path id="page" d="M18.4 21H5.6V3h7.8l5 4.9V21zM7.1 19.5h9.8V8.6l-4-4.1H7.1v15z" /><path id="corner" d="M11.5 5.4v4.3h4.4" /></g></svg>,
};

export const ANIMATION_DURATION_DEFAULTS = {
	drop: 1600,
	'fade-in': 500,
	'fly-in-bottom': 500,
	'fly-in-left': 500,
	'fly-in-right': 500,
	'fly-in-top': 500,
	pulse: 500,
	'rotate-in-left': 700,
	'rotate-in-right': 700,
	'twirl-in': 1000,
	'whoosh-in-left': 500,
	'whoosh-in-right': 500,
	'pan-left': 1000,
	'pan-right': 1000,
	'pan-down': 1000,
	'pan-up': 1000,
	'zoom-in': 1000,
	'zoom-out': 1000,
};

export const BLOCK_TAG_MAPPING = {
	'core/button': 'div.wp-block-button',
	'core/code': 'pre',
	'core/embed': 'figure',
	'core/image': '.wp-block-image',
	'amp/amp-story-text': 'p,h1,h2',
	'core/preformatted': 'pre',
	'core/pullquote': 'blockquote',
	'core/quote': 'blockquote',
	'core/table': 'table',
	'core/verse': 'pre',
	'core/video': 'figure',
};

export const AMP_ANIMATION_TYPE_OPTIONS = [
	{
		value: '',
		label: __( 'None', 'amp' ),
	},
	{
		value: 'drop',
		label: __( 'Drop', 'amp' ),
	},
	{
		value: 'fade-in',
		label: __( 'Fade In', 'amp' ),
	},
	{
		value: 'fly-in-bottom',
		label: __( 'Fly In Bottom', 'amp' ),
	},
	{
		value: 'fly-in-left',
		label: __( 'Fly In Left', 'amp' ),
	},
	{
		value: 'fly-in-right',
		label: __( 'Fly In Right', 'amp' ),
	},
	{
		value: 'fly-in-top',
		label: __( 'Fly In Top', 'amp' ),
	},
	{
		value: 'pulse',
		label: __( 'Pulse', 'amp' ),
	},
	{
		value: 'rotate-in-left',
		label: __( 'Rotate In Left', 'amp' ),
	},
	{
		value: 'rotate-in-right',
		label: __( 'Rotate In Right', 'amp' ),
	},
	{
		value: 'twirl-in',
		label: __( 'Twirl In', 'amp' ),
	},
	{
		value: 'whoosh-in-left',
		label: __( 'Whoosh In Left', 'amp' ),
	},
	{
		value: 'whoosh-in-right',
		label: __( 'Whoosh In Right', 'amp' ),
	},
	{
		value: 'pan-left',
		label: __( 'Pan Left', 'amp' ),
	},
	{
		value: 'pan-right',
		label: __( 'Pan Right', 'amp' ),
	},
	{
		value: 'pan-down',
		label: __( 'Pan Down', 'amp' ),
	},
	{
		value: 'pan-up',
		label: __( 'Pan Up', 'amp' ),
	},
	{
		value: 'zoom-in',
		label: __( 'Zoom In', 'amp' ),
	},
	{
		value: 'zoom-out',
		label: __( 'Zoom Out', 'amp' ),
	},
];

export const AMP_STORY_FONTS = [
	{
		value: '',
		label: __( 'None', 'amp' ),
	},
	{
		value: 'arial',
		label: __( 'Arial', 'amp' ),
	},
	{
		value: 'arial-black',
		label: __( 'Arial Black', 'amp' ),
	},
	{
		value: 'arial-narrow',
		label: __( 'Arial Narrow', 'amp' ),
	},
	{
		value: 'baskerville',
		label: __( 'Baskerville', 'amp' ),
	},
	{
		value: 'brush-script-mt',
		label: __( 'Brush Script MT', 'amp' ),
	},
	{
		value: 'copperplate',
		label: __( 'Copperplate', 'amp' ),
	},
	{
		value: 'courier-new',
		label: __( 'Courier New', 'amp' ),
	},
	{
		value: 'century-gothic',
		label: __( 'Century Gothic', 'amp' ),
	},
	{
		value: 'garamond',
		label: __( 'Garamond', 'amp' ),
	},
	{
		value: 'georgia',
		label: __( 'Georgia', 'amp' ),
	},
	{
		value: 'gill-sans',
		label: __( 'Gill Sans', 'amp' ),
	},
	{
		value: 'lucida-bright',
		label: __( 'Lucida Bright', 'amp' ),
	},
	{
		value: 'lucida-sans-typewriter',
		label: __( 'Lucida Sans Typewriter', 'amp' ),
	},
	{
		value: 'papyrus',
		label: __( 'Papyrus', 'amp' ),
	},
	{
		value: 'palatino',
		label: __( 'Palatino', 'amp' ),
	},
	{
		value: 'tahoma',
		label: __( 'Tahoma', 'amp' ),
	},
	{
		value: 'times-new-roman',
		label: __( 'Times New Roman', 'amp' ),
	},
	{
		value: 'trebuchet-ms',
		label: __( 'Trebuchet MS', 'amp' ),
	},
	{
		value: 'verdana',
		label: __( 'Verdana', 'amp' ),
	},
];
