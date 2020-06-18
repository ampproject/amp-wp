/**
 * WordPress dependencies
 */
import React, { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import './index.css';
import {
	getProtectedBranches,
	getPullRequestsWithBuilds,
} from '../../utils/github';
import { BuildSelector } from '../build-selector';

export default class Container extends Component {
	constructor( props ) {
		super( props );

		// Default build options to choose from.
		const buildOptions = [
			{
				label: __( 'Latest release', 'amp-qa-tester' ),
				value: 'release',
				origin: 'release',
			},
			{
				label: __( 'Develop branch', 'amp-qa-tester' ),
				value: 'develop',
				origin: 'branch',
			},
		];

		this.state = {
			isLoading: true,
			isSwitching: false,
			isDevBuild: false,
			error: null,
			buildOption: { label: '', value: '' },
			buildOptions,
		};

		this.handleActivation = this.handleActivation.bind( this );
		this.handleChangeDevBuild = this.handleChangeDevBuild.bind( this );
		this.handleChangeBuildOption = this.handleChangeBuildOption.bind(
			this
		);
	}

	componentDidMount() {
		Promise.all( [
			this.addReleaseBranchOptions(),
			this.addPullRequestOptions(),
		] ).then( () => {
			this.setState( { isLoading: false } );
		} );
	}

	componentWillUnmount() {
		clearTimeout( this.pageReloadTimeout );
	}

	/**
	 * Append to the default build options a list of release branches that would have build zips available for download.
	 */
	async addReleaseBranchOptions() {
		// Retrieve the list of protected branches.
		const branches = await getProtectedBranches();

		// Filter the release branches.
		const releaseBranchNames = branches
			.map( ( branch ) => branch.name )
			.filter( ( branchName ) => /[0-9]+\.[0-9]+/.test( branchName ) );

		// We only need the release branches 1.5 and after, since only they would have plugin builds.
		const releasesWithBuilds = releaseBranchNames
			.sort()
			.slice( releaseBranchNames.indexOf( '1.5' ) );

		// Create build options from list of release branches with plugin builds.
		const branchOptions = releasesWithBuilds.map( ( branch ) => {
			return {
				label: `${ branch } release branch`,
				value: branch,
				origin: 'branch',
			};
		} );

		this.setState( {
			buildOptions: this.state.buildOptions.concat( branchOptions ),
		} );
	}

	/**
	 * Append to the default build options a list of PRs that have build zips available for download.
	 */
	async addPullRequestOptions() {
		const pullRequests = await getPullRequestsWithBuilds();

		// Create build options from list of PRs with plugin builds.
		const prOptions = pullRequests.map( ( pr ) => {
			return {
				label: `PR #${ pr.number }: ${ pr.title }`,
				value: pr.number,
				origin: 'pr',
			};
		} );

		this.setState( {
			buildOptions: this.state.buildOptions.concat( prOptions ),
		} );
	}

	/**
	 * Set the selected build option.
	 *
	 * @param {Object} newOption Selected build option.
	 */
	handleChangeBuildOption( newOption ) {
		this.setState( { buildOption: newOption } );
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
				origin: buildOption.origin,
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
				<BuildSelector
					buildOptions={ buildOptions }
					onOptionSelect={ this.handleChangeBuildOption }
				/>

				{ 'release' !== buildOption.value && (
					<div className="amp-qa-tester-checkbox">
						<input
							id="amp-qa-tester-is-development-build"
							type="checkbox"
							checked={ this.state.isDevBuild }
							onChange={ this.handleChangeDevBuild }
						/>

						<label htmlFor="amp-qa-tester-is-development-build">
							{ __( 'Development build', 'amp-qa-tester' ) }
						</label>
					</div>
				) }

				<button
					className="button button-primary"
					disabled={
						buildOption.value === '' || isSwitching || error
					}
					onClick={ this.handleActivation }
				>
					{ __( 'Activate selection', 'amp-qa-tester' ) }
				</button>

				{ isSwitching && (
					<div>
						<div className="switching" />
						{ __( 'Switching versions…', 'amp-qa-tester' ) }
					</div>
				) }

				{ error && (
					<div className="error">
						<span>
							{ /* eslint-disable-next-line prettier/prettier */ }
							{ `${ __( 'Error', 'amp-qa-tester' ) }: ${ error }` }
						</span>
						<button
							type="button"
							className="notice-dismiss"
							onClick={ () => this.setState( { error: null } ) }
						/>
					</div>
				) }
			</>
		);
	}
}
