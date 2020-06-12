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

class AdminBar extends Component {
	constructor( props ) {
		super( props );

		// Default build options to choose from.
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
		clearTimeout( this.pageReloadTimeout );
	}

	/**
	 * Fetches all open PRs that have build zips available for download.
	 *
	 * @return {Promise} Promise containing a list of PR items.
	 */
	getPullRequests() {
		const url = new URL( 'https://api.github.com/search/issues' );
		const params = {
			q:
				'repo:ampproject/amp-wp is:pr is:open commenter:app/github-actions in:comments "Download development build"',
			sort: 'created',
			order: 'desc',
		};

		url.search = new URLSearchParams( params ).toString();

		return fetch( url )
			.then( ( response ) => response.json() )
			.then( ( json ) => json.items || [] );
	}

	/**
	 * Append to the default build options a list of PRs that have build zips available for download.
	 */
	addPullRequestOptions() {
		// Retrieve the PRs from GitHub and append them to the list of builds above.
		this.getPullRequests().then( ( results ) => {
			const prOptions = results.map( ( pr ) => {
				return {
					label: `PR #${ pr.number }: ${ pr.title }`,
					value: pr.number,
				};
			} );

			this.setState( {
				isLoading: false,
				buildOptions: this.state.buildOptions.concat( prOptions ),
			} );
		} );
	}

	/**
	 * Set the selected build option.
	 *
	 * @param {Event<HTMLSelectElement>} event Change event.
	 */
	handleChangeBuildOption( event ) {
		const value = event.target.value;
		const buildOption = this.state.buildOptions.find(
			( option ) => value === option.value.toString()
		);

		if ( undefined !== buildOption ) {
			this.setState( { buildOption } );
		}
	}

	/**
	 * Set whether a dev build is needed or not.
	 *
	 * @param {Event<HTMLInputElement>} event Change event.
	 */
	handleChangeDevBuild( event ) {
		const checked = event.target.checked;
		this.setState( { isDevBuild: checked } );
	}

	/**
	 * Switches the AMP plugin to the selected build option.
	 */
	handleActivation() {
		const { isDevBuild, buildOption } = this.state;
		this.setState( { isSwitching: true } );

		apiFetch( {
			path: '/amp-qa-tester/v1/switch',
			method: 'POST',
			data: {
				id: buildOption.value,
				isDev: isDevBuild,
			},
		} )
			.then( () => {
				// @todo Could this be handled better?
				// Set a 1 second delay before reloading the page to allow for the new plugin to activate without any error.
				this.pageReloadTimeout = setTimeout( () => {
					window.location.reload();
				}, 1000 );
			} )
			.catch( ( error ) => {
				if ( error.message ) {
					this.showError( error.message );
				} else {
					this.showError();
				}
			} );
	}

	/**
	 * Display an error message.
	 *
	 * @param {string} message Message.
	 */
	showError( message = '' ) {
		if ( ! message ) {
			message = __(
				'An unknown error occurred while switching build',
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
		document.getElementById( 'amp-qa-tester-build-selector' )
	);
} );
