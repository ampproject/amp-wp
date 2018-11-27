/* exported ampImageNoCaption */
/* global lodash */
/* eslint no-magic-numbers: [ "error", { "ignore": [ 0, -1 ] } ] */

let ampImageCaptionToggle = ( function() { // eslint-disable-line no-unused-vars
	let component, __;

	__ = wp.i18n.__;

	component = {

		/**
		 * Holds data.
		 */
		data: {}
	};

	/**
	 * Add filters.
	 */
	component.boot = function boot() {
		wp.hooks.addFilter( 'editor.BlockListBlock', 'ampStoryEditorBlocks/ampImageCaptionToggle/addImageCaptionProps', component.addImageCaptionProps );
		wp.hooks.addFilter( 'editor.BlockEdit', 'ampStoryEditorBlocks/ampImageCaptionToggle/addToggleControl', component.addToggleControl );
		wp.hooks.addFilter( 'blocks.registerBlockType', 'ampStoryEditorBlocks/ampImageCaptionToggle/addAttributes', component.addCaptionAttribute );

	};

	/**
	 * Add wrapper props to the AMP Image No Caption Block.
	 *
	 * @param {Object} BlockListBlock BlockListBlock element.
	 * @return {Function} Handler.
	 */
	component.addImageCaptionProps = function( BlockListBlock ) {
		let el = wp.element.createElement;
		return function( props ) {
			if ( 'core/image' !== props.block.name ) {
				return el(
					BlockListBlock,
					props
				);
			}

			let caption = 'noCaption';
			if ( props.block.attributes.ampShowImageCaption ) {
				caption = 'showCaption';
			}
			let newProps = lodash.assign(
				{},
				props,
				{
					wrapperProps: lodash.assign(
						{},
						props.wrapperProps,
						{
							'data-amp-image-caption': caption
						}
					)
				}
			);

			return el(
				BlockListBlock,
				newProps
			);
		};
	};

	/**
	 * Add AMP Image Caption Attribute.
	 *
	 * @param {Object} settings Settings.
	 * @param {string} name Block name.
	 * @return {Object} Settings.
	 */
	component.addCaptionAttribute = function addAMPAttributes( settings, name ) {
		if ( 'core/image' !== name ) {
			return settings;
		}

		if ( ! settings.attributes ) {
			settings.attributes = {};
		}
		settings.attributes.ampShowImageCaption = {
			type: 'boolean'
		};

		return settings;
	};

	/**
	 * Add the toggle control to the image controls.
	 *
	 * @param {Function} BlockEdit Edit function.
	 * @return {Function} Edit function.
	 */
	component.addToggleControl = function filterBlocksEdit( BlockEdit ) {
		let el = wp.element.createElement;

		return function( props ) {
			let attributes = props.attributes,
				name = props.name,
				inspectorControls,
				InspectorControls = wp.editor.InspectorControls,
				PanelBody = wp.components.PanelBody,
				ToggleControl = wp.components.ToggleControl,
				ampShowImageCaption = !! attributes.ampShowImageCaption;

			if ( 'core/image' !== name ) {
				// Return original.
				return [
					InspectorControls,
					el( BlockEdit, _.extend( {
						key: 'original'
					}, props ) )
				];
			}


			inspectorControls = el( InspectorControls, { key: 'inspector' },
				el( PanelBody, { title: __( 'Toggle Image Caption', 'amp' ), key: 'amp-image-no-caption' },
					el( ToggleControl, {
						key: 'position',
						label: __( 'Show or hide the caption', 'amp' ),
						checked: ampShowImageCaption,
						onChange: function( value ) {
							let newValue = !! value,
								theDiv = document.getElementById( 'block-' + props.clientId );
							if ( true === newValue ) {
								theDiv.setAttribute( 'data-amp-image-caption', 'hasCaption' );
							} else {
								theDiv.setAttribute( 'data-amp-image-caption', 'noCaption' );
							}
							props.setAttributes( { ampShowImageCaption: !! value } );
						},
						help: __( 'Toggle on to show image caption', 'amp' ),
					} ),
				)
			);

			return [
				inspectorControls,
				el( BlockEdit, _.extend( {
					key: 'original'
				}, props ) )
			];
		};
	};

	return component;
}() );
