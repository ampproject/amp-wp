/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Arimo from '../../images/font-names/arimo.svg';
import Lato from '../../images/font-names/lato.svg';
import Lora from '../../images/font-names/lora.svg';
import Merriweather from '../../images/font-names/merriweather.svg';
import Montserrat from '../../images/font-names/montserrat.svg';
import NotoSans from '../../images/font-names/noto-sans.svg';
import OpenSans from '../../images/font-names/open-sans.svg';
import OpenSansCondensed from '../../images/font-names/open-sans-condensed.svg';
import Oswald from '../../images/font-names/oswald.svg';
import PlayfairDisplay from '../../images/font-names/playfair-display.svg';
import PtSans from '../../images/font-names/pt-sans.svg';
import PtSansNarrow from '../../images/font-names/pt-sans-narrow.svg';
import PtSerif from '../../images/font-names/pt-serif.svg';
import Raleway from '../../images/font-names/raleway.svg';
import Roboto from '../../images/font-names/roboto.svg';
import RobotoCondensed from '../../images/font-names/roboto-condensed.svg';
import RobotoSlab from '../../images/font-names/roboto-slab.svg';
import Slabo27 from '../../images/font-names/slabo-27.svg';
import SourceSansPro from '../../images/font-names/source-sans-pro.svg';
import Ubuntu from '../../images/font-names/ubuntu.svg';

export const STORY_PAGE_INNER_WIDTH = 328;
export const STORY_PAGE_INNER_HEIGHT = 553;

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
];

export const ALLOWED_BLOCKS = [
	...ALLOWED_TOP_LEVEL_BLOCKS,
	...ALLOWED_CHILD_BLOCKS,
];

export const IMAGE_BACKGROUND_TYPE = 'image';
export const VIDEO_BACKGROUND_TYPE = 'video';
export const ALLOWED_MEDIA_TYPES = [ 'image', 'video/mp4' ];
export const POSTER_ALLOWED_MEDIA_TYPES = [ 'image' ];
export const MEDIA_INNER_BLOCKS = [ 'core/video', 'core/audio' ];
export const MAX_IMAGE_SIZE_SLUG = 'amp_story_page';
export const MEGABYTE_IN_BYTES = 1000000;
export const VIDEO_ALLOWED_MEGABYTES_PER_SECOND = 1;

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

export const AMP_STORY_FONT_IMAGES = {
	Arimo,
	Lato,
	Lora,
	Merriweather,
	Montserrat,
	'Noto Sans': NotoSans,
	'Open Sans': OpenSans,
	'Open Sans Condensed': OpenSansCondensed,
	Oswald,
	'Playfair Display': PlayfairDisplay,
	'PT Sans': PtSans,
	'PT Sans Narrow': PtSansNarrow,
	'PT Serif': PtSerif,
	Raleway,
	Roboto,
	'Roboto Condensed': RobotoCondensed,
	'Roboto Slab': RobotoSlab,
	'Slabo 27px': Slabo27,
	'Source Sans Pro': SourceSansPro,
	Ubuntu,
};

export const TEXT_BLOCK_BORDER = 5;
export const TEXT_BLOCK_PADDING = 7;
