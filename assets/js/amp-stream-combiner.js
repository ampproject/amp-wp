"use strict";
/* This JS file will be added as an inline script in a stream header fragment response. */

/**
 * Apply the stream body data to the stream header.
 *
 * @param {Array}  data Data.
 * @param {Array}  data.head_nodes      - Nodes in HEAD.
 * @param {Object} data.body_attributes - Attributes on body.
 */
function ampStreamCombine( data ) { // eslint-disable-line no-unused-vars
	var removedElements, i, element;

	/* @todo Take the data and actually apply the changes. */
	for ( i = 0; i < data.head_nodes.length; i++ ) {
		if ( 'title' === data.head_nodes[ i ][ 0 ] ) {
			document.title = data.head_nodes[ i ][ 2 ]; // This should be crafted for actual
		}
	}

	/* Purge all traces of the stream combination logic to ensure the AMP validator doesn't complain at runtime. */
	removedElements = [
		'amp-stream-fragment-boundary',
		'amp-stream-combine-function',
		'amp-stream-combine-call'
	];
	for ( i = 0; i < removedElements.length; i++ ) {
		element = document.getElementById( removedElements[ i ] );
		if ( element ) {
			element.parentNode.removeChild( element );
		}
	}
}
