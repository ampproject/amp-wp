/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';

domReady( () => {
	const errorDetailList = document.querySelector( 'details > dl.detailed' );

	addAdditionalErrorDetails( errorDetailList );
	unwrapErrorDetails( errorDetailList );
} );

const addAdditionalErrorDetails = ( errorDetailList ) => {
	addStatusDetail( errorDetailList );
};

const addStatusDetail = ( errorDetails ) => {
	const validationStatus = document.querySelector( 'span.status-text' );

	const term = document.createElement( 'dt' );
	term.textContent = __( 'Status', 'amp' );

	const detail = document.createElement( 'dd' );
	detail.append( validationStatus );

	errorDetails.prepend( term, detail );
};

const unwrapErrorDetails = ( errorDetailList ) => {
	const detailsElement = errorDetailList.parentNode;
	detailsElement.parentNode.append( errorDetailList );
	detailsElement.style.display = 'none';
};
