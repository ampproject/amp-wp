/* global _ */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, 1, 100 ] } ] */

import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	RangeControl,
	PanelBody,
	Toolbar,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import {
	PanelColorSettings,
	InspectorControls,
	InnerBlocks,
	BlockTitle,
} from '@wordpress/editor';
import { select } from '@wordpress/data';

const {
	getBlockRootClientId,
} = select( 'core/editor' );

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
	'zoom-out': 1000,
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
	'core/video',
];

export const BLOCK_ICONS = {
	'amp/amp-story-page': <svg id="story-page-icon" viewBox="0 0 24 24"><g id="icon" fill="#181D21"><path id="page" d="M18.4 21H5.6V3h7.8l5 4.9V21zM7.1 19.5h9.8V8.6l-4-4.1H7.1v15z" /><path id="corner" d="M11.5 5.4v4.3h4.4" /></g></svg>,
	'amp/amp-story-cta-layer': <svg id="cta-layer-icon" viewBox="0 0 24 24"><g id="icon_1_" fill="#181D21"><path id="page_1_" d="M5.6 3v18h12.8V3H5.6zm11.3 16.5H7.1v-3.9h9.8v3.9zm-9.8-5.4V4.5h9.8v9.6H7.1z" /><path id="action" d="M9.1 16.8h5.8v1.5H9.1v-1.5z" /></g></svg>,
	'amp/amp-story-grid-layer-horizontal': <svg id="horizontal-layer-icon" viewBox="0 0 24 24"><g id="icon" fill="#181D21"><path id="page" d="M18.4 21H5.6V3h12.8v18zM7.1 19.5h9.8v-15H7.1v15z" /><path d="M8.8 6H11v12H8.8V6z" /></g></svg>,
	'amp/amp-story-grid-layer-vertical': <svg id="vertical-layer-icon" viewBox="0 0 24 24"><g id="icon" fill="#181D21"><path id="page" d="M18.4 21H5.6V3h12.8v18zM7.1 19.5h9.8v-15H7.1v15z" /><path d="M15.4 6.4v2.2H8.6V6.4h6.8z" /></g></svg>,
	'amp/amp-story-grid-layer-thirds': <svg id="thirds-layer-icon" viewBox="0 0 24 24"><g id="icon_1_" fill="#181D21"><path id="page_1_" d="M18.4 21H5.6V3h12.8v18zM7.1 19.5h9.8v-15H7.1v15z" /><path d="M15.3 6.5v2.2H8.5V6.5h6.8zm0 4.4v2.2H8.5v-2.2h6.8zm0 4.3v2.2H8.5v-2.2h6.8z" /></g></svg>,
	'amp/amp-story-grid-layer-background-image': <svg id="image-fill-layer-icon" viewBox="0 0 24 24"><g id="icon_1_" fill="#181D21"><path d="M18.4 21H5.6V3h12.8v18zM7.1 19.5h9.8v-15H7.1v15z" /><path id="mountains" d="M8.2 14.1L10 11l1.4 2 1.9-3.1 2.3 4.2H8.2z" /></g></svg>,
	'amp/amp-story-grid-layer-background-video': <svg id="video-fill-layer-icon" viewBox="0 0 24 24"><g id="icon_1_" fill="#181D21"><path id="page_1_" d="M18.4 21H5.6V3h12.8v18zM7.1 19.5h9.8v-15H7.1v15z" /><path id="arrow_1_" d="M12 8.8c-1.8 0-3.2 1.4-3.2 3.2s1.4 3.2 3.2 3.2 3.2-1.4 3.2-3.2-1.4-3.2-3.2-3.2zm-1.3 4.8v-3.2l3.2 1.7c.1 0-3.2 1.5-3.2 1.5z" /></g></svg>,
};

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @param {Function} setAttributes Set Attributes.
 * @param {Object} attributes Props.
 * @return {[XML,*,XML,*,XML]} Controls.
 */
export function getAmpStoryAnimationControls( setAttributes, attributes ) {
	const placeHolder = ANIMATION_DEFAULTS[ attributes.animationType ] || 0;
	return [
		<SelectControl
			key="animation"
			label={ __( 'Animation Type', 'amp' ) }
			value={ attributes.animationType }
			options={ [
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
			] }
			onChange={ ( value ) => ( setAttributes( { animationType: value } ) ) }
		/>,
		<RangeControl
			key="duration"
			label={ __( 'Duration (ms)', 'amp' ) }
			value={ attributes.animationDuration ? parseInt( attributes.animationDuration ) : '' }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDuration: value } );
			} }
			min="0"
			max="5000"
			placeholder={ placeHolder }
			initialPosition={ placeHolder }
		/>,
		<RangeControl
			key="delay"
			label={ __( 'Delay (ms)', 'amp' ) }
			value={ parseInt( attributes.animationDelay ) }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDelay: value } );
			} }
			min="0"
			max="5000"
		/>,
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
	const onChangeBackgroundColor = ( newBackgroundColor ) => {
		setAttributes( { backgroundColor: newBackgroundColor } );
	};

	return [
		<PanelColorSettings
			title={ __( 'Background Color Settings', 'amp' ) }
			initialOpen={ false }
			key="bgColor"
			colorSettings={ [
				{
					value: attributes.backgroundColor,
					onChange: onChangeBackgroundColor,
					label: __( 'Background Color', 'amp' ),
				},
			] }
		/>,
		<RangeControl
			key="opacity"
			label={ __( 'Layer Opacity (%)', 'amp' ) }
			value={ parseInt( attributes.opacity * 100 ) }
			onChange={ ( value ) => {
				value = value / 100;
				setAttributes( { opacity: value } );
			} }
			min="0"
			max="100"
			placeholder="100"
			initialPosition="100"
		/>,
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
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in',
		},
		animationDuration: {
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in-duration',
		},
		animationDelay: {
			source: 'attribute',
			selector: 'amp-story-grid-layer',
			attribute: 'animate-in-delay',
			default: '0ms',
		},
		backgroundColor: {
			type: 'string',
		},
		opacity: {
			default: 1,
		},
	};
}

/**
 * Save method for Grid Layers.
 *
 * @param {Object} attributes Block attributes.
 * @param {string} template Template type: fill, vertical, horizontal, thirds.
 * @return {XML} Content to save.
 */
export function saveGridLayer( attributes, template ) {
	const layerProps = {
			template: template,
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

/**
 * Function for grid layers' block Edit.
 *
 * @param {Object} props Properties.
 * @param {string} type Template type: vertical, horizontal, thirds.
 * @return {[XML,XML]} Edit.
 */
export function editGridLayer( props, type ) {
	const { setAttributes, attributes, isSelected } = props;
	const rootClientId = getBlockRootClientId( props.clientId );
	return [
		<InspectorControls key="inspector">
			{
				getAmpGridLayerBackgroundSettings( setAttributes, attributes )
			}
			<PanelBody key="animation" title={ __( 'Grid Layer Animation', 'amp' ) }>
				{
					getAmpStoryAnimationControls( setAttributes, attributes )
				}
			</PanelBody>
		</InspectorControls>,
		isSelected && (
			getLayerBreadCrumb( props.clientId, rootClientId )
		),
		<div key="contents" style={ { opacity: attributes.opacity, backgroundColor: attributes.backgroundColor } } className={ 'amp-grid-template amp-grid-template-' + type }>
			<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
		</div>,
	];
}

/**
 * Function for grid fill layers' block Edit.
 *
 * @param {Object} props Properties.
 * @param {Array} template Gutenberg block template.
 * @return {[XML,XML]} Edit.
 */
export function editFillLayer( props, template ) {
	const { setAttributes, attributes, isSelected } = props;

	return [
		<InspectorControls key="inspector">
			{
				getAmpGridLayerBackgroundSettings( setAttributes, attributes )
			}
			<PanelBody key="animation" title={ __( 'Layer Animation', 'amp' ) }>
				{
					getAmpStoryAnimationControls( setAttributes, attributes )
				}
			</PanelBody>
		</InspectorControls>,
		isSelected && (
			getLayerBreadCrumb( props.clientId, getBlockRootClientId( props.clientId ) )
		),
		<div key="contents" style={ { opacity: attributes.opacity, backgroundColor: attributes.backgroundColor } } className={ 'amp-grid-template amp-grid-template-fill' }>
			<InnerBlocks template={ template } templateLock="all" />
		</div>,
	];
}

/**
 * Get Layer Breadcrumb.
 *
 * @param {string} clientId Layer ID.
 * @param {string} rootClientId Parent ID.
 * @return {XML} Breadcrumb.
 */
function getLayerBreadCrumb( clientId, rootClientId ) {
	return (
		<div key="breadcrumb" className={ 'editor-block-list__breadcrumb' }>
			<Toolbar>
				{ rootClientId && (
					<Fragment>
						<BlockTitle clientId={ rootClientId } />
						<span className="editor-block-list__descendant-arrow" />
					</Fragment>
				) }
				<BlockTitle clientId={ clientId } />
			</Toolbar>
		</div>
	);
}
