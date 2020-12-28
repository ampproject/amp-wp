/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { noop } from 'lodash';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from 'amp-block-validation';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { Error } from '..';
import { createStore } from '../../store';

let container, pluginBlock, themeBlock, coreBlock, unknownBlock;

const TEST_PLUGIN_BLOCK = 'my-plugin/test-block';
const TEST_MU_PLUGIN_BLOCK = 'my-mu-plugin/test-block';
const TEST_THEME_BLOCK = 'my-theme/test-block';
const TEST_CORE_BLOCK = 'core/test-block';
const TEST_UNKNOWN_BLOCK = 'unknown/test-block';

global.URL = class {};

registerBlockType( TEST_PLUGIN_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test plugin block',
} );

registerBlockType( TEST_MU_PLUGIN_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test mu-plugin block',
} );

registerBlockType( TEST_THEME_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test theme block',
} );

registerBlockType( TEST_CORE_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test core block',
} );

registerBlockType( TEST_UNKNOWN_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test unknown block',
} );

function createTestStoreAndBlocks() {
	pluginBlock = createBlock( TEST_PLUGIN_BLOCK, {} );
	themeBlock = createBlock( TEST_THEME_BLOCK, {} );
	coreBlock = createBlock( TEST_CORE_BLOCK, {} );
	unknownBlock = createBlock( TEST_UNKNOWN_BLOCK, {} );

	dispatch( 'core/block-editor' ).insertBlocks( [ pluginBlock, themeBlock, coreBlock, unknownBlock ] );

	createStore( {
		reviewLink: 'http://site.test/wp-admin',
		validationErrors: [
			{
				clientId: pluginBlock.clientId,
				code: 'DISALLOWED_TAG',
				status: 3,
				term_id: 12,
				title: 'Invalid script: <code>jquery.js</code>',
				error: {
					type: 'js_error',
					sources: [],
				},
			},
			{
				clientId: themeBlock.clientId,
				code: 'DISALLOWED_TAG',
				status: 3,
				term_id: 12,
				title: 'Invalid script: <code>jquery.js</code>',
				error: {
					type: 'js_error',
				},
			},
			{
				clientId: coreBlock.clientId,
				code: 'DISALLOWED_TAG',
				status: 3,
				term_id: 12,
				title: 'Invalid script: <code>jquery.js</code>',
				error: {
					type: 'js_error',
					sources: [],
				},
			},
			{
				clientId: unknownBlock.clientId,
				code: 'DISALLOWED_TAG',
				status: 3,
				term_id: 12,
				title: 'Invalid script: <code>jquery.js</code>',
				error: {
					type: 'js_error',
					sources: [],
				},
			},
		],
	} );
}

function getTestBlock( type ) {
	switch ( type ) {
		case 'plugin':
			return pluginBlock;

		case 'theme':
			return themeBlock;

		case 'core':
			return coreBlock;

		case 'unknown':
			return unknownBlock;

		default:
			return null;
	}
}

describe( 'Error', () => {
	beforeAll( () => {
		createTestStoreAndBlocks();
	} );

	beforeEach( () => {
		container = document.createElement( 'ul' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it.each( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
	].map( ( status ) => [
		status,
		() => (
			<Error
				status={ status }
				term_id={ 12 }
				title="My test error"
				error={ { type: 'js_error', sources: [] } }
			/>
		),
	] ) )( 'errors with no associated blocks work correctly', ( status, ErrorComponent ) => {
		act( () => {
			render(
				<ErrorComponent />,
				container,
			);
		} );

		const newReviewed = [ VALIDATION_ERROR_NEW_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS ].includes( status ) ? 'new' : 'reviewed';

		expect( container.querySelector( 'li' ).getAttribute( 'class' ) ).toBe( 'amp-error-container' );
		expect( container.querySelectorAll( `.amp-error--${ newReviewed }` ) ).toHaveLength( 1 );
		expect( container.querySelector( '.amp-error__details-link' ) ).toBeNull();
		expect( container.querySelector( `.amp-error--${ newReviewed } button` ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__block-type-icon' ) ).toBeNull();

		container.querySelector( `.amp-error--${ newReviewed } button` ).click();
		expect( container.querySelector( '.amp-error__details-link' ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__select-block' ) ).toBeNull();
	} );

	it.each( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
	].map( ( status ) => [
		status,
		() => (
			<Error
				clientId={ pluginBlock.clientId }
				status={ status }
				term_id={ 12 }
				title="My test error"
				error={ { type: 'js_error' } }
			/>
		),
	] ) )( 'errors with associated blocks work correctly', ( status, ErrorComponent ) => {
		act( () => {
			render(
				<ErrorComponent />,
				container,
			);
		} );

		const newReviewed = [ VALIDATION_ERROR_NEW_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS ].includes( status ) ? 'new' : 'reviewed';

		expect( container.querySelector( 'li' ).getAttribute( 'class' ) ).toBe( 'amp-error-container' );
		expect( container.querySelectorAll( `.amp-error--${ newReviewed }` ) ).toHaveLength( 1 );
		expect( container.querySelector( '.amp-error__details-link' ) ).toBeNull();
		expect( container.querySelector( `.amp-error--${ newReviewed } button` ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__block-type-icon' ) ).not.toBeNull();

		container.querySelector( `.amp-error--${ newReviewed } button` ).click();
		expect( container.querySelector( '.amp-error__details-link' ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__select-block' ) ).not.toBeNull();
	} );
} );

describe( 'ErrorTypeIcon', () => {
	beforeEach( () => {
		container = document.createElement( 'ul' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it.each(
		[
			'js_error',
			'html_attribute_error',
			'html_element_error',
			'css_error',
			'unknown_error',
		],
	)( 'shows the correct error icon', ( errorType ) => {
		act( () => {
			render(
				<Error
					status={ 3 }
					term_id={ 12 }
					title="My test error"
					error={ { type: errorType } }
				/>,
				container,
			);
		} );

		let expectedClass;
		switch ( errorType ) {
			case 'html_attribute_error':
				expectedClass = '.amp-error__error-type-icon--html-attribute-error';
				break;

			case 'html_element_error':
				expectedClass = '.amp-error__error-type-icon--html-element-error';
				break;

			case 'js_error':
				expectedClass = '.amp-error__error-type-icon--js-error';
				break;

			case 'css_error':
				expectedClass = '.amp-error__error-type-icon--css-error';
				break;

			default:
				expectedClass = null;
		}

		if ( ! expectedClass ) {
			expect( container.querySelector( 'svg[class^=amp-error__error-type-icon]' ) ).toBeNull();
		} else {
			expect( container.querySelector( expectedClass ) ).not.toBeNull();
		}
	} );
} );

describe( 'ErrorContent', () => {
	beforeAll( () => {
		createTestStoreAndBlocks();
	} );

	beforeEach( () => {
		container = document.createElement( 'ul' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it.each( [
		null,
		'plugin',
		'mu-plugin',
		'theme',
		'core',
	].reduce(
		( collection, testBlockSource ) => [
			...collection,
			...[
				VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
				VALIDATION_ERROR_ACK_REJECTED_STATUS,
				VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
				VALIDATION_ERROR_NEW_REJECTED_STATUS,
			].map(
				( status ) => [ testBlockSource, status ],
			),
		],
		[],
	) )( 'shows expected content based on whether or not the error has an associated block', ( testBlockSource, status ) => {
		const clientId = getTestBlock( testBlockSource )?.clientId || null;

		render(
			<Error
				clientId={ clientId }
				status={ status }
				term_id={ 12 }
				title="My test error"
				error={ { type: 'js_error', sources: [] } }
			/>,
			container,
		);

		container.querySelector( `.components-button` ).click();

		expect( container.innerHTML ).toContain( 'Markup status' );

		if ( null === clientId ) {
			expect( container.innerHTML ).toContain( 'outside the post content' );
			return;
		}

		expect( container.innerHTML ).toContain( '<dt>Source' );
		expect( container.innerHTML ).not.toContain( 'outside the post content' );

		switch ( testBlockSource ) {
			case 'plugin':
				expect( container.innerHTML ).toContain( 'test plugin block' );
				expect( container.innerHTML ).toContain( 'My plugin (plugin)' );
				break;

			case 'mu-plugin':
				expect( container.innerHTML ).toContain( 'test mu-plugin block' );
				expect( container.innerHTML ).toContain( 'My MU plugin (must-use plugin)' );
				break;

			case 'theme':
				expect( container.innerHTML ).toContain( 'test theme block' );
				expect( container.innerHTML ).toContain( 'My theme (theme)' );
				break;

			case 'core':
				expect( container.innerHTML ).toContain( 'test core block' );
				expect( container.innerHTML ).toContain( '<dd>WordPress core' );
				break;

			default:
				break;
		}

		expect( container.innerHTML ).toContain(
			[ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ? 'Removed' : 'Kept',
		);

		expect( container.innerHTML ).not.toContain(
			[ VALIDATION_ERROR_ACK_REJECTED_STATUS, VALIDATION_ERROR_NEW_REJECTED_STATUS ].includes( status ) ? 'Removed' : 'Kept',
		);

		container.querySelector( '.amp-error__select-block' ).click();
		expect( select( 'core/block-editor' ).getSelectedBlock().clientId ).toBe( clientId );
	} );
} );
