/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Arimo from '../images/font-names/arimo.svg';
import Lato from '../images/font-names/lato.svg';
import Lora from '../images/font-names/lora.svg';
import Merriweather from '../images/font-names/merriweather.svg';
import Montserrat from '../images/font-names/montserrat.svg';
import NotoSans from '../images/font-names/noto-sans.svg';
import OpenSans from '../images/font-names/open-sans.svg';
import OpenSansCondensed from '../images/font-names/open-sans-condensed.svg';
import Oswald from '../images/font-names/oswald.svg';
import PlayfairDisplay from '../images/font-names/playfair-display.svg';
import PtSans from '../images/font-names/pt-sans.svg';
import PtSansNarrow from '../images/font-names/pt-sans-narrow.svg';
import PtSerif from '../images/font-names/pt-serif.svg';
import Raleway from '../images/font-names/raleway.svg';
import Roboto from '../images/font-names/roboto.svg';
import RobotoCondensed from '../images/font-names/roboto-condensed.svg';
import RobotoSlab from '../images/font-names/roboto-slab.svg';
import Slabo27 from '../images/font-names/slabo-27.svg';
import SourceSansPro from '../images/font-names/source-sans-pro.svg';
import Ubuntu from '../images/font-names/ubuntu.svg';

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
		label: 'Arial',
	},
	{
		value: 'arial-black',
		label: 'Arial Black',
	},
	{
		value: 'arial-narrow',
		label: 'Arial Narrow',
	},
	{
		value: 'arimo',
		label: 'Arimo',
		element: Arimo,
	},
	{
		value: 'baskerville',
		label: 'Baskerville',
	},
	{
		value: 'brush-script-mt',
		label: 'Brush Script MT',
	},
	{
		value: 'copperplate',
		label: 'Copperplate',
	},
	{
		value: 'courier-new',
		label: 'Courier New',
	},
	{
		value: 'century-gothic',
		label: 'Century Gothic',
	},
	{
		value: 'garamond',
		label: 'Garamond',
	},
	{
		value: 'georgia',
		label: 'Georgia',
	},
	{
		value: 'gill-sans',
		label: 'Gill Sans',
	},
	{
		value: 'lato',
		label: 'Lato',
		element: Lato,
	},
	{
		value: 'lora',
		label: 'Lora',
		element: Lora,
	},
	{
		value: 'lucida-bright',
		label: 'Lucida Bright',
	},
	{
		value: 'lucida-sans-typewriter',
		label: 'Lucida Sans Typewriter',
	},
	{
		value: 'merriweather',
		label: 'Merriweather',
		element: Merriweather,
	},
	{
		value: 'montserrat',
		label: 'Montserrat',
		element: Montserrat,
	},
	{
		value: 'noto-sans',
		label: 'Noto Sans',
		element: NotoSans,
	},
	{
		value: 'open-sans',
		label: 'Open Sans',
		element: OpenSans,
	},
	{
		value: 'open-sans-condensed',
		label: 'Open Sans Condensed',
		element: OpenSansCondensed,
	},
	{
		value: 'oswald',
		label: 'Oswald',
		element: Oswald,
	},
	{
		value: 'papyrus',
		label: 'Papyrus',
	},
	{
		value: 'palatino',
		label: 'Palatino',
	},
	{
		value: 'playfair-display',
		label: 'Playfair Display',
		element: PlayfairDisplay,
	},
	{
		value: 'pt-sans',
		label: 'PT Sans',
		element: PtSans,
	},
	{
		value: 'pt-sans-narrow',
		label: 'PT Sans Narrow',
		element: PtSansNarrow,
	},
	{
		value: 'pt-serif',
		label: 'PT Serif',
		element: PtSerif,
	},
	{
		value: 'raleway',
		label: 'Raleway',
		element: Raleway,
	},
	{
		value: 'roboto',
		label: 'Roboto',
		element: Roboto,
	},
	{
		value: 'roboto-condensed',
		label: 'Roboto Condensed',
		element: RobotoCondensed,
	},
	{
		value: 'roboto-slab',
		label: 'Roboto Slab',
		element: RobotoSlab,
	},
	{
		value: 'slabo-27px',
		label: 'Slabo 27px',
		element: Slabo27,
	},
	{
		value: 'source-sans-pro',
		label: 'Source Sans Pro',
		element: SourceSansPro,
	},
	{
		value: 'tahoma',
		label: 'Tahoma',
	},
	{
		value: 'times-new-roman',
		label: 'Times New Roman',
	},
	{
		value: 'trebuchet-ms',
		label: 'Trebuchet MS',
	},
	{
		value: 'ubuntu',
		label: 'Ubuntu',
		element: Ubuntu,
	},
	{
		value: 'verdana',
		label: 'Verdana',
	},
];
