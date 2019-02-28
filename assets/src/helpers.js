import { __, _x } from '@wordpress/i18n';

export const ALLOWED_BLOCKS = [
	'core/audio',
	'core/code',
	'core/embed',
	'core/image',
	'core/list',
	'core/paragraph',
	'core/preformatted',
	'core/pullquote',
	'core/quote',
	'core/table',
	'core/verse',
	'core/video',
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
	'core/paragraph': 'p',
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
		label: _x( 'Arial', 'font name', 'amp' ),
	},
	{
		value: 'arial-black',
		label: _x( 'Arial Black', 'font name', 'amp' ),
	},
	{
		value: 'arial-narrow',
		label: _x( 'Arial Narrow', 'font name', 'amp' ),
	},
	{
		value: 'arimo',
		label: _x( 'Arimo', 'font name', 'amp' ),
	},
	{
		value: 'baskerville',
		label: _x( 'Baskerville', 'font name', 'amp' ),
	},
	{
		value: 'brush-script-mt',
		label: _x( 'Brush Script MT', 'font name', 'amp' ),
	},
	{
		value: 'copperplate',
		label: _x( 'Copperplate', 'font name', 'amp' ),
	},
	{
		value: 'courier-new',
		label: _x( 'Courier New', 'font name', 'amp' ),
	},
	{
		value: 'century-gothic',
		label: _x( 'Century Gothic', 'font name', 'amp' ),
	},
	{
		value: 'garamond',
		label: _x( 'Garamond', 'font name', 'amp' ),
	},
	{
		value: 'georgia',
		label: _x( 'Georgia', 'font name', 'amp' ),
	},
	{
		value: 'gill-sans',
		label: _x( 'Gill Sans', 'font name', 'amp' ),
	},
	{
		value: 'lato',
		label: _x( 'Lato', 'font name', 'amp' ),
	},
	{
		value: 'lora',
		label: _x( 'Lora', 'font name', 'amp' ),
	},
	{
		value: 'lucida-bright',
		label: _x( 'Lucida Bright', 'font name', 'amp' ),
	},
	{
		value: 'lucida-sans-typewriter',
		label: _x( 'Lucida Sans Typewriter', 'font name', 'amp' ),
	},
	{
		value: 'merriweather',
		label: _x( 'Merriweather', 'font name', 'amp' ),
	},
	{
		value: 'montserrat',
		label: _x( 'Montserrat', 'font name', 'amp' ),
	},
	{
		value: 'noto-sans',
		label: _x( 'Noto Sans', 'font name', 'amp' ),
	},
	{
		value: 'open-sans',
		label: _x( 'Open Sans', 'font name', 'amp' ),
	},
	{
		value: 'open-sans-condensed',
		label: _x( 'Open Sans Condensed', 'font name', 'amp' ),
	},
	{
		value: 'oswald',
		label: _x( 'Oswald', 'font name', 'amp' ),
	},
	{
		value: 'papyrus',
		label: _x( 'Papyrus', 'font name', 'amp' ),
	},
	{
		value: 'palatino',
		label: _x( 'Palatino', 'font name', 'amp' ),
	},
	{
		value: 'playfair-display',
		label: _x( 'Playfair Display', 'font name', 'amp' ),
	},
	{
		value: 'pt-sans',
		label: _x( 'PT Sans', 'font name', 'amp' ),
	},
	{
		value: 'pt-sans-narrow',
		label: _x( 'PT Sans Narrow', 'font name', 'amp' ),
	},
	{
		value: 'pt-serif',
		label: _x( 'PT Serif', 'font name', 'amp' ),
	},
	{
		value: 'raleway',
		label: _x( 'Raleway', 'font name', 'amp' ),
	},
	{
		value: 'roboto',
		label: _x( 'Roboto', 'font name', 'amp' ),
	},
	{
		value: 'roboto-condensed',
		label: _x( 'Roboto Condensed', 'font name', 'amp' ),
	},
	{
		value: 'roboto-slab',
		label: _x( 'Roboto Slab', 'font name', 'amp' ),
	},
	{
		value: 'slabo-27',
		label: _x( 'Slabo 27px', 'font name', 'amp' ),
	},
	{
		value: 'source-sans-pro',
		label: _x( 'Source Sans Pro', 'font name', 'amp' ),
	},
	{
		value: 'tahoma',
		label: _x( 'Tahoma', 'font name', 'amp' ),
	},
	{
		value: 'times-new-roman',
		label: _x( 'Times New Roman', 'font name', 'amp' ),
	},
	{
		value: 'trebuchet-ms',
		label: _x( 'Trebuchet MS', 'font name', 'amp' ),
	},
	{
		value: 'ubuntu',
		label: _x( 'Ubuntu', 'font name', 'amp' ),
	},
	{
		value: 'verdana',
		label: _x( 'Verdana', 'font name', 'amp' ),
	},
];
