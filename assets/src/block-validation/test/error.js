/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';
import { registerBlockType, createBlock } from '@wordpress/blocks';
import '@wordpress/editor';

/**
 * Internal dependencies
 */
import { Error } from '../error';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from '../constants';
import { createStore } from '../store';

let container, pluginBlock, themeBlock, coreBlock, unknownBlock;

const TEST_PLUGIN_BLOCK = 'my-plugin/test-block';
const TEST_THEME_BLOCK = 'my-theme/test-block';
const TEST_CORE_BLOCK = 'core/test-block';
const TEST_UNKNOWN_BLOCK = 'unknown/test-block';

jest.mock( '../use-inline-data', () => ( {
	useInlineData: () => ( {
		blockSources: {
			// Note: values must be hardcoded in mock function.
			'my-plugin/test-block': {
				source: 'plugin',
				name: 'My plugin',
			},
			'my-theme/test-block': {
				source: 'theme',
				name: 'My theme',
			},
			'core/test-block': {
				source: 'core',
				name: null,
			},
			'unknown/test-block': {
				source: 'unknown',
				name: null,
			},
		},
		CSS_ERROR_TYPE: 'css_error',
		HTML_ATTRIBUTE_ERROR_TYPE: 'html_attribute_error',
		HTML_ELEMENT_ERROR_TYPE: 'html_element_error',
		JS_ERROR_TYPE: 'js_error',
	} ),
} ) );

registerBlockType( TEST_PLUGIN_BLOCK, {
	attributes: {},
	save: noop,
	category: 'widgets',
	title: 'test plugin block',
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
		validationErrors: [
			{
				clientId: pluginBlock.clientId,
				code: 'DISALLOWED_TAG',
				status: 3,
				term_id: 12,
				title: 'Invalid script: <code>jquery.js</code>',
				error: {
					type: 'js_error',
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
				error={ { type: 'js_error' } }
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
			case 'html_element_error':
				expectedClass = '.amp-error__html-error-icon';
				break;

			case 'js_error':
				expectedClass = '.amp-error__js-error-icon';
				break;

			case 'css_error':
				expectedClass = '.amp-error__css-error-icon';
				break;

			default:
				expectedClass = null;
		}

		if ( ! expectedClass ) {
			expect( container.querySelector( 'svg[class^=amp-error]' ) ).toBeNull();
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
		'theme',
		'core',
		'unknown',
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
				error={ { type: 'js_error' } }
			/>,
			container,
		);

		container.querySelector( `.components-button` ).click();

		expect( container.innerHTML ).toContain( 'Markup status' );

		if ( null === clientId ) {
			expect( container.innerHTML ).toContain( 'outside the post content' );
			expect( container.innerHTML ).not.toContain( 'Source' );
			return;
		}

		expect( container.innerHTML ).toContain( '<dt>Source' );
		expect( container.innerHTML ).not.toContain( 'outside the post content' );

		switch ( testBlockSource ) {
			case 'plugin':
				expect( container.innerHTML ).toContain( 'test plugin block' );
				expect( container.innerHTML ).toContain( 'My plugin (plugin)' );
				break;

			case 'theme':
				expect( container.innerHTML ).toContain( 'test theme block' );
				expect( container.innerHTML ).toContain( 'My theme (theme)' );
				break;

			case 'core':
				expect( container.innerHTML ).toContain( 'test core block' );
				expect( container.innerHTML ).toContain( '<dd>WordPress core' );
				break;

			case 'unknown':
				expect( container.innerHTML ).toContain( 'test unknown block' );
				expect( container.innerHTML ).toContain( '<dd>unknown' );
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
