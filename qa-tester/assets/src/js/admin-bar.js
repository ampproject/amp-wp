/**
 * WordPress dependencies
 */
import React, { Component, render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../css/admin-bar.css';
import getPullRequests from './get-pulls';

class AdminBar extends Component {
	constructor( props ) {
		super( props );

		const buildOptions = [
			{
				label: __( 'Latest release', 'amp-qa-tester' ),
				value: 'release',
			},
			{
				label: __( 'Develop branch', 'amp-qa-tester' ),
				value: 'develop',
			},
		];

		this.state = {
			isLoading: true,
			isSwitching: false,
			isDevBuild: false,
			error: null,
			buildOption: { value: '' },
			buildOptions,
		};

		this.addPullRequestOptions();
	}

	componentWillUnmount() {
		clearTimeout( this.errorTimeout );
	}

	addPullRequestOptions() {
		// Retrieve the PRs from GitHub and append them to the list of builds above.
		getPullRequests().then( ( pulls ) => {
			const pullOptions = pulls.map( ( pull ) => {
				return {
					label: `PR #${ pull.number }: ${ pull.title }`,
					value: pull.url,
				};
			} );

			this.setState( {
				isLoading: false,
				buildOptions: this.state.buildOptions.concat( pullOptions ),
			} );
		} );
	}

	handleChangeBuildOption( event ) {
		const value = event.target.value;
		const buildOption = this.state.buildOptions.find(
			( option ) => value === option.value
		);

		this.setState( { buildOption } );
	}

	handleChangeDevBuild( event ) {
		const checked = event.target.checked;
		this.setState( { isDevBuild: checked } );
	}

	handleActivation() {
		const { isDevBuild, buildOption } = this.state;
		this.setState( { isSwitching: true } );

		apiFetch( {
			path: '/amp-qa-tester/v1/switch',
			method: 'POST',
			data: {
				url: buildOption.value,
				isDevBuild,
			},
		} )
			.then( () => {
				window.location.reload();
			} )
			.catch( ( error ) => {
				if ( error.message ) {
					this.showError( error.message );
				} else {
					this.showError();
				}
			} );
	}

	showError( message = null ) {
		if ( ! message ) {
			message = __(
				'An unknown error occurred activating build',
				'amp-qa-tester'
			);
		}

		this.setState( {
			isSwitching: false,
			error: message,
		} );

		this.errorTimeout = setTimeout( () => {
			this.setState( { error: null } );
		}, 5000 );
	}

	render() {
		const {
			isLoading,
			isSwitching,
			error,
			buildOption,
			buildOptions,
		} = this.state;

		if ( isLoading ) {
			return <p>{ __( 'Loading…', 'amp-qa-tester' ) }</p>;
		}

		return (
			<>
				{ /* eslint-disable-next-line jsx-a11y/no-onchange */ }
				<select
					value={ buildOption.value }
					onChange={ this.handleChangeBuildOption.bind( this ) }
				>
					<option disabled={ true } value="">
						{ __( 'Select a version…', 'amp-qa-tester' ) }
					</option>
					{ buildOptions.map( ( option, index ) => (
						<option key={ index } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>

				{ 'release' !== buildOption.value && (
					<div className={ 'amp-qa-tester-checkbox' }>
						<input
							id="amp-qa-tester-is-development-build"
							type="checkbox"
							checked={ this.state.isDevBuild }
							onChange={ this.handleChangeDevBuild.bind( this ) }
						/>

						<label htmlFor="amp-qa-tester-is-development-build">
							{ __( 'Development build', 'amp-qa-tester' ) }
						</label>
					</div>
				) }

				<button
					className={ 'button button-primary' }
					disabled={
						buildOption.value === '' || isSwitching || error
					}
					onClick={ this.handleActivation.bind( this ) }
				>
					{ __( 'Activate selection', 'amp-qa-tester' ) }
				</button>

				{ isSwitching && (
					<div>
						<div className={ 'switching' } />
						{ __( 'Switching versions…', 'amp-qa-tester' ) }
					</div>
				) }

				{ error && (
					<div className={ 'error' }>
						{ `${ __( 'Error', 'amp-qa-tester' ) }: ${ error }` }
					</div>
				) }
			</>
		);
	}
}

domReady( () => {
	render(
		<AdminBar />,
		document.getElementById( 'amp-qa-tester-pull-request-selector' )
	);
} );
