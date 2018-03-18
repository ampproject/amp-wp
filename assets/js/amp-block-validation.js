/*jshint esversion: 6 */
/*global _, wp:true */
/**
 * AMP Gutenberg integration.
 *
 * On editing a block, this checks that the content is AMP-compatible.
 * And it displays a notice if it's not.
 */

/* exported ampBlockValidation */
var ampBlockValidation = ( function( $ ) {
	var module = {

		/**
		 * Holds data.
		 */
		data: {},

		/**
		 * Boot module.
		 *
		 * @param {Object} data Object data.
		 * @return {void}
		 */
		boot: function( data ) {
			module.data = data;
			$( document ).ready( function() {
				if ( 'undefined' !== typeof wp.blocks ) {
					module.processBlocks();
				}
			} );
		},

		/**
		 * Gets all of the registered blocks, and overwrites their edit() functions.
		 *
		 * The new edit() functions will check if the content is AMP-compliant.
		 * If not, it will display a notice.
		 *
		 * @returns {void}
		 */
		processBlocks: function() {
			var blocks = wp.blocks.getBlockTypes();
			blocks.forEach( function( block ) {
				if ( block.hasOwnProperty( 'name' ) ) {
					module.overwriteEdit( block.name );
				}
			} );
		},

		/**
		 * Overwrites the edit() function of a block.
		 *
		 * Outputs the original edit function, stored in OriginalBlockEdit.
		 * This also appends a notice to the block.
		 * It only displays if the block's content isn't valid AMP,
		 *
		 * @see https://riad.blog/2017/10/16/one-thousand-and-one-way-to-extend-gutenberg-today/
		 * @param {string} blockType the type of the block, like 'core/paragraph'.
		 * @returns {void}
		 */
		overwriteEdit: function( blockType ) {
			let block = wp.blocks.unregisterBlockType( blockType );
			let OriginalBlockEdit = block.edit;

			block.edit = class AMPNotice extends wp.element.Component {

				/**
				 * The AMPNotice constructor.
				 *
				 * @param {object} props The component properties.
				 * @returns {void}
				 */
				constructor( props ) {
					props.attributes.pendingValidation = false;
					super( props );
					this.validateAMP = _.throttle( this.validateAMP, 5000 );
					this.state = { isInvalidAMP: false };
				}

				/**
				 * Outputs the existing block, with a Notice element below.
				 *
				 * The Notice only appears if the state of isInvalidAMP is false.
				 * It also displays the name of the block.
				 *
				 * @returns {array} The elements.
				 */
				render() {
					let originalEdit;
					let result;

					result = [];
					originalEdit = wp.element.createElement( OriginalBlockEdit, this.props );
					if ( originalEdit ) {
						result.push( originalEdit );
					}
					if ( this.state.isInvalidAMP && wp.components.hasOwnProperty( 'Notice' ) ) {
						result.push( wp.element.createElement(
							wp.components.Notice,
							{
								status: 'warning',
								content: module.data.i18n.notice.replace( '%s', this.props.name ),
								isDismissible: false
							}
						) );
					}

					this.props.attributes.pendingValidation = false;
					return result;
				}

				/**
				 * Handler for after the component mounting.
				 *
				 * If validateAMP() changes the isInvalidAMP state, it will result in this method being called again.
				 * There's no need to check if the state is valid again.
				 * So this skips the check if pendingValidation is true.
				 *
				 * @returns {void}
				 */
				componentDidMount() {
					if ( ! this.props.attributes.pendingValidation ) {
						let content = this.props.attributes.content;
						if ( 'string' !== typeof content ) {
							content = wp.element.renderToString( content );
						}
						if ( content.length > 0 ) {
							this.validateAMP( this.props.attributes.content );
						}
					}
					this.props.attributes.pendingValidation = false;
				}

				/**
				 * Validates the content for AMP compliance, and sets the state of the Notice.
				 *
				 * Depending on the results of the validation,
				 * it sets the Notice component's isInvalidAMP state.
				 * This will cause the notice to either display or be hidden.
				 *
				 * @param {string} content The block content, from calling save().
				 * @returns {void}
				 */
				validateAMP( content ) {
					this.setState( function() {

								// Changing the state can cause componentDidMount() to be called, so prevent it from calling validateAMP() again.
								component.props.attributes.pendingValidation = true;
								return { isInvalidAMP: ( Math.random() > 0.5 ) };
							} );

					let component = this;
					$.post(
						module.data.endpoint,
						{
							markup: content
						}
					).done( function( data ) {
						if ( data.hasOwnProperty( 'removed_elements' ) && ( 0 === data.removed_elements.length ) && ( 0 === data.removed_attributes.length ) ) {
							component.setState( function() {

								// Changing the state can cause componentDidMount() to be called, so prevent it from calling validateAMP() again.
								component.props.attributes.pendingValidation = true;
								return { isInvalidAMP: false };
							} );
						} else {
							component.setState( function() {
								component.props.attributes.pendingValidation = true;
								return { isInvalidAMP: true };
							} );
						}
					} );
				}

			};

			wp.blocks.registerBlockType( blockType, block );
		}

	};

	return module;

} )( window.jQuery );
