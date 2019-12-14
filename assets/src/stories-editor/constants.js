/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export const STORY_PAGE_INNER_WIDTH = 328;
export const STORY_PAGE_INNER_HEIGHT = 553;
export const STORY_PAGE_INNER_HEIGHT_FOR_CTA = Math.floor( STORY_PAGE_INNER_HEIGHT / 5 );
export const STORY_PAGE_MARGIN = 50;

export const MIN_BLOCK_WIDTH = 40;
export const MIN_BLOCK_HEIGHTS = {
	default: 30,
	'core/pullquote': 180,
	'core/table': 100,
	'core/code': 45,
};

export const ALLOWED_TOP_LEVEL_BLOCKS = [
	'amp/amp-story-page',
	'core/block', // Reusable blocks.
	'core/template', // Reusable blocks.
];

export const ALLOWED_MOVABLE_BLOCKS = [
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
	'amp/amp-story-post-author',
	'amp/amp-story-post-date',
	'amp/amp-story-post-title',
	'core/html',
	'core/block', // Reusable blocks.
	'core/template', // Reusable blocks.
];

export const BLOCKS_WITH_META_CONTENT = [
	'amp/amp-story-post-author',
	'amp/amp-story-post-date',
	'amp/amp-story-post-title',
];

export const BLOCKS_WITH_TEXT_SETTINGS = [
	'amp/amp-story-text',
	'amp/amp-story-post-author',
	'amp/amp-story-post-date',
	'amp/amp-story-post-title',
];

export const BLOCKS_WITH_COLOR_SETTINGS = [
	'amp/amp-story-text',
	'amp/amp-story-post-author',
	'amp/amp-story-post-date',
	'amp/amp-story-post-title',
	'amp/amp-story-cta',
	'amp/amp-story-page-attachment',
];

export const BLOCKS_WITH_RESIZING = [
	'core/code',
	'core/embed',
	'core/image',
	'core/list',
	'core/pullquote',
	'core/quote',
	'core/table',
	'core/video',
	'amp/amp-story-text',
	'amp/amp-story-post-author',
	'amp/amp-story-post-date',
	'amp/amp-story-post-title',
];

export const ALLOWED_CHILD_BLOCKS = [
	...ALLOWED_MOVABLE_BLOCKS,
	'amp/amp-story-cta',
	'amp/amp-story-page-attachment',
];

export const ALLOWED_BLOCKS = [
	...ALLOWED_TOP_LEVEL_BLOCKS,
	...ALLOWED_CHILD_BLOCKS,
];

export const DISABLE_DUPLICATE_BLOCKS = [
	'amp/amp-story-cta',
	'amp/amp-story-page-attachment',
];

export const IMAGE_BACKGROUND_TYPE = 'image';
export const VIDEO_BACKGROUND_TYPE = 'video';

export const POSTER_ALLOWED_MEDIA_TYPES = [ IMAGE_BACKGROUND_TYPE ];

export const MEDIA_INNER_BLOCKS = [ 'core/video', 'core/audio' ];
export const MAX_IMAGE_SIZE_SLUG = 'amp_story_page';

export const ANIMATION_DURATION_DEFAULTS = {
	drop: 1600,
	'fade-in': 400,
	'fly-in-bottom': 400,
	'fly-in-left': 400,
	'fly-in-right': 400,
	'fly-in-top': 400,
	pulse: 400,
	'rotate-in-left': 600,
	'rotate-in-right': 600,
	'twirl-in': 1000,
	'whoosh-in-left': 400,
	'whoosh-in-right': 400,
	'pan-left': 1000,
	'pan-right': 1000,
	'pan-down': 1000,
	'pan-up': 1000,
	'zoom-in': 1000,
	'zoom-out': 1000,
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

export const REVERSE_WIDTH_CALCULATIONS = [
	'left',
	'bottomLeft',
	'topLeft',
];

export const REVERSE_HEIGHT_CALCULATIONS = [
	'top',
	'topRight',
	'topLeft',
];

export const TEXT_BLOCK_PADDING = 7;

export const BLOCK_ROTATION_SNAPS = [ -180, -165, -150, -135, -120, -105, -90, -75, -60, -45, -30, -15, 0, 15, 30, 45, 60, 75, 90, 105, 120, 135, 150, 165, 180 ];
export const BLOCK_ROTATION_SNAP_GAP = 10;

export const BLOCK_RESIZING_SNAP_GAP = 8;
export const BLOCK_DRAGGING_SNAP_GAP = 8;
