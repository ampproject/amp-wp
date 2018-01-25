/*jshint esversion: 6 */
/*global amp, wp:true */
/**
 * AMP Gutenberg integration.
 *
 * On editing a block, this checks that the content is AMP-compatible.
 * And it displays a notice if it's not.
 */

/* exported ampGutenberg */
var ampGutenberg = ( function( $ ) {
	'use strict';

	var component = {
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
			component.data = data;
			$( document ).ready( function() {
				component.getScript();
			} );
		},

		/**
		 * Gets the amp CDN validator.js script, and run this file.
		 *
		 * The getScript callback is a workaround, and not recommended for production.
		 * It replaces the wp value that the script overwrote.
		 *
		 * @returns void.
		 */
		getScript: function() {
			var previousWp = wp;
			$.getScript( 'https://cdn.ampproject.org/v0/validator.js', function() {
				wp = previousWp;
				component.processBlocks();
			} );
		},

		/**
		 * Gets all of the registered blocks, and overwrites their edit() functions.
		 *
		 * THe new edit() functions will check if the content is AMP-compliant.
		 * If not, the block will display a notice.
		 *
		 * @returns {void}
		 */
		processBlocks: function() {
			var blocks = wp.blocks.getBlockTypes(),
				key = 'name';
			blocks.forEach( function( block ) {
				if ( block.hasOwnProperty( key ) ) {
					component.overwriteEdit( block[ key ] );
				}
			} );
		},

		/**
		 * Overwrites the edit() function of a block.
		 *
		 * Retain the original edit function in OriginalBlockEdit.
		 * If the block's content isn't valid AMP,
		 * Prepend a notice to the block.
		 *
		 * @see https://riad.blog/2017/10/16/one-thousand-and-one-way-to-extend-gutenberg-today/
		 * @param {string} blockType the type of the block, like 'core/paragraph'.
		 * @returns {void}
		 */
		overwriteEdit: function( blockType ) {
			var el = wp.element.createElement,
				Notice = wp.components.Notice,
				block = wp.blocks.unregisterBlockType( blockType ),
				OriginalBlockEdit = block.edit;

			block.edit = function( props ) {
				var result = [ el( OriginalBlockEdit, props ) ],
					content = block.save( props );

				if ( 'string' !== typeof content ) {
					content = wp.element.renderToString( content );
				}

				// If validation fails, prepend a Notice to the block.
				if ( ! component.isValidAMP( content ) ) {
					result.unshift( el(
						Notice,
						{
							status: 'warning',
							content: component.data.i18n.notice,
							isDismissible: false
						}
					) );
				}
				return result;
			};
			wp.blocks.registerBlockType( blockType, block );
		},

		/**
		 * Whether markup is valid AMP.
		 *
		 * Uses the AMP validator from the AMP CDN.
		 * And places the passed markup inside the <body> tag of a basic valid AMP page.
		 * Then, validates that page.
		 *
		 * @param {string} markup The markup to test.
		 * @returns {boolean} $valid Whether the passed markup is valid AMP.
		 */
		isValidAMP: function( markup ) {
			var ampDocument = `<!doctype html><html ⚡><head><meta charset="utf-8"><link rel="canonical" href="./regular-html-version.html"><meta name="viewport" content="width=device-width,minimum-scale=1"><style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript><script async src="https://cdn.ampproject.org/v0.js"></script></head><body>${markup}</body></html>`,
				validated = amp.validator.validateString( ampDocument ),
				validKey = 'status';
			return ( validated.hasOwnProperty( validKey ) && 'PASS' === validated[ validKey ] );
		}
	};

	return {
		boot: component.boot
	};

} )( window.jQuery );
