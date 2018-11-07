/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, 1, 100 ] } ] */

const { __ } = wp.i18n;
const {
	SelectControl,
	RangeControl
} = wp.components;

const {
	PanelColorSettings,
	InnerBlocks
} = wp.editor;

const ANIMATION_DEFAULTS = {
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
	'zoom-out': 1000
};

export const ALLOWED_BLOCKS = [
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
	'core/video'
];

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @param {Function} setAttributes Set Attributes.
 * @param {Object} attributes Props.
 * @return {[XML,*,XML,*,XML]} Controls.
 */
export function getAmpStoryAnimationControls( setAttributes, attributes ) {
	let placeHolder = ANIMATION_DEFAULTS[ attributes.animationType ] || 0;
	return [
		<SelectControl
			key="animation"
			label={ __( 'Animation Type', 'amp' ) }
			value={ attributes.animationType }
			options={ [
				{
					value: '',
					label: __( 'None', 'amp' )
				},
				{
					value: 'drop',
					label: __( 'Drop', 'amp' )
				},
				{
					value: 'fade-in',
					label: __( 'Fade In', 'amp' )
				},
				{
					value: 'fly-in-bottom',
					label: __( 'Fly In Bottom', 'amp' )
				},
				{
					value: 'fly-in-left',
					label: __( 'Fly In Left', 'amp' )
				},
				{
					value: 'fly-in-right',
					label: __( 'Fly In Right', 'amp' )
				},
				{
					value: 'fly-in-top',
					label: __( 'Fly In Top', 'amp' )
				},
				{
					value: 'pulse',
					label: __( 'Pulse', 'amp' )
				},
				{
					value: 'rotate-in-left',
					label: __( 'Rotate In Left', 'amp' )
				},
				{
					value: 'rotate-in-right',
					label: __( 'Rotate In Right', 'amp' )
				},
				{
					value: 'twirl-in',
					label: __( 'Twirl In', 'amp' )
				},
				{
					value: 'whoosh-in-left',
					label: __( 'Whoosh In Left', 'amp' )
				},
				{
					value: 'whoosh-in-right',
					label: __( 'Whoosh In Right', 'amp' )
				},
				{
					value: 'pan-left',
					label: __( 'Pan Left', 'amp' )
				},
				{
					value: 'pan-right',
					label: __( 'Pan Right', 'amp' )
				},
				{
					value: 'pan-down',
					label: __( 'Pan Down', 'amp' )
				},
				{
					value: 'pan-up',
					label: __( 'Pan Up', 'amp' )
				},
				{
					value: 'zoom-in',
					label: __( 'Zoom In', 'amp' )
				},
				{
					value: 'zoom-out',
					label: __( 'Zoom Out', 'amp' )
				}
			] }
			onChange={ value => ( setAttributes( { animationType: value } ) ) }
		/>,
		<RangeControl
			key='duration'
			label={ __( 'Duration (ms)', 'amp' ) }
			value={ attributes.animationDuration ? parseInt( attributes.animationDuration ) : '' }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDuration: value } );
			} }
			min='0'
			max='5000'
			placeholder={ placeHolder }
			initialPosition={ placeHolder }
		/>,
		<RangeControl
			key='delay'
			label={ __( 'Delay (ms)', 'amp' ) }
			value={ parseInt( attributes.animationDelay ) }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDelay: value } );
			} }
			min='0'
			max='5000'
		/>
	];
}

/**
 * Get background settings fof grid layers.
 *
 * @param {Function} setAttributes Set attributes function.
 * @param {Object} attributes Attributes object.
 * @return {[XML,XML]} Array of elements.
 */
export function getAmpGridLayerBackgroundSettings( setAttributes, attributes ) {
	const onChangeBackgroundColor = newBackgroundColor => {
		setAttributes( { backgroundColor: newBackgroundColor } );
	};

	return [
		<PanelColorSettings
			title={ __( 'Background Color Settings', 'amp' ) }
			initialOpen={ false }
			key='bgColor'
			colorSettings={ [
				{
					value: attributes.backgroundColor,
					onChange: onChangeBackgroundColor,
					label: __( 'Background Color', 'amp' )
				}
			] }
		/>,
		<RangeControl
			key='opacity'
			label={ __( 'Layer Opacity (%)', 'amp' ) }
			value={ parseInt( attributes.opacity * 100 ) }
			onChange={ ( value ) => {
				value = value / 100;
				setAttributes( { opacity: value } );
			} }
			min='0'
			max='100'
			placeholder='100'
			initialPosition='100'
		/>
	];
}

/**
 * Get attributes for all grid layers.
 *
 * @return {Object} Attributes object.
 */
export function getGridLayerAttributes() {
	return {
		animationType: {
			type: 'string',
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in'
		},
		animationDuration: {
			type: 'string',
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in-duration'
		},
		animationDelay: {
			type: 'string',
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in-delay',
			default: '0ms'
		},
		backgroundColor: {
			type: 'string'
		},
		opacity: {
			type: 'number',
			default: 1
		}
	};
}

/**
 * Save method for Fill Grid Layers.
 *
 * @param {Object} attributes Block attributes.
 * @return {XML} Content to save.
 */
export function saveFillGridLayer( attributes ) {
	let layerProps = {
			template: 'fill'
		},
		style = {};
	if ( attributes.animationType ) {
		layerProps[ 'animate-in' ] = attributes.animationType;

		if ( attributes.animationDelay ) {
			layerProps[ 'animate-in-delay' ] = attributes.animationDelay;
		}
		if ( attributes.animationDuration ) {
			layerProps[ 'animate-in-duration' ] = attributes.animationDuration;
		}
	}

	if ( 1 !== attributes.opacity ) {
		style.opacity = attributes.opacity;
	}
	if ( attributes.backgroundColor ) {
		style.backgroundColor = attributes.backgroundColor;
	}
	if ( ! _.isEmpty( style ) ) {
		layerProps.style = style;
	}

	return (
		<amp-story-grid-layer { ...layerProps }>
			<InnerBlocks.Content />
		</amp-story-grid-layer>
	);
}
