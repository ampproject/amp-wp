
/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TemplateModeOption } from '..';
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';
import { OptionsContextProvider } from '../../options-context-provider';

jest.mock( '../../../components/options-context-provider' );

let container;

describe( 'TemplateModeOption', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshot', () => {
		let wrapper = create(
			<OptionsContextProvider>
				<TemplateModeOption details="Component details" mode={ STANDARD }>
					<div>
						{ 'Component children' }
					</div>
				</TemplateModeOption>
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<OptionsContextProvider>
				<TemplateModeOption details="Component details" mode={ READER } previouslySelected={ true } />
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<OptionsContextProvider>
				<TemplateModeOption
					details="Component details"
					mode={ READER }
					previouslySelected={ true }
					initialOpen={ true }
					labelExtra={ (
						<div>
							{ 'Extra label content' }
						</div>
					) }
				/>
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'is open by default if is current mode', () => {
		// Reader is the default in mock options context provider.
		act( () => {
			render(
				<OptionsContextProvider>
					<TemplateModeOption mode={ READER }>
						<div id="reader-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
					<TemplateModeOption mode={ STANDARD }>
						<div id="standard-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
				</OptionsContextProvider>,
				container,
			);
		} );

		expect( container.querySelector( '#reader-mode-children' ) ).not.toBeNull();
		expect( container.querySelector( '#standard-mode-children' ) ).toBeNull();
	} );

	it( 'is open by default if initialOpen is true', () => {
		// Reader is the default in mock options context provider.
		act( () => {
			render(
				<OptionsContextProvider>
					<TemplateModeOption mode={ TRANSITIONAL }>
						<div id="reader-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
					<TemplateModeOption mode={ STANDARD } initialOpen={ true }>
						<div id="standard-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
				</OptionsContextProvider>,
				container,
			);
		} );

		expect( container.querySelector( '#transitional-mode-children' ) ).toBeNull();
		expect( container.querySelector( '#standard-mode-children' ) ).not.toBeNull();
	} );

	it( 'can be open', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<TemplateModeOption mode={ TRANSITIONAL } initialOpen={ true }>
						<div id="transitional-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
				</OptionsContextProvider>,
				container,
			);
		} );

		expect( container.querySelector( '#transitional-mode-children' ) ).not.toBeNull();
	} );

	it( 'can be closed', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<TemplateModeOption mode={ TRANSITIONAL } initialOpen={ false }>
						<div id="transitional-mode-children">
							{ 'children' }
						</div>
					</TemplateModeOption>
				</OptionsContextProvider>,
				container,
			);
		} );

		expect( container.querySelector( '#transitional-mode-children' ) ).toBeNull();
	} );
} );
