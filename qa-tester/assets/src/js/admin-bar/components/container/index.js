/**
 * WordPress dependencies
 */
import React, { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import './style.scss';
import {
	getProtectedBranches,
	getPullRequestsWithBuilds,
	getReleases,
} from '../../utils/github';
import { BuildSelector } from '../build-selector';

export default class Container extends Component {
	constructor( props ) {
		super( props );

		// Default build options to choose from.
		const buildOptions = [
			{
				label: __( 'Develop branch', 'amp-qa-tester' ),
				value: 'develop',
				origin: 'branch',
			},
		];

		this.state = {
			isLoading: null,
			isInstalling: false,
			isDevBuild: false,
			error: null,
			buildOption: { label: '', value: '' },
			buildOptions,
		};

		this.adminBarSubmenuWrapper = document.getElementById( 'amp-qa-tester-adminbar' );

		this.dismissError = this.dismissError.bind( this );
		this.handleInstallation = this.handleInstallation.bind( this );
		this.handleChangeDevBuild = this.handleChangeDevBuild.bind( this );
		this.handleChangeBuildOption = this.handleChangeBuildOption.bind( this );
	}

	componentDidMount() {
		// Only fetch build options when hovering over submenu the first time.
		global.jQuery( '#wp-admin-bar-amp-qa-tester' ).hoverIntent( {
			over: () => {
				if ( null !== this.state.isLoading ) {
					return;
				}

				this.setState( { isLoading: true } );

				Promise.all( [
					this.addGitHubReleases(),
					this.addReleaseBranchOptions(),
					this.addPullRequestOptions(),
				] )
					.then( () => {
						this.setState( { isLoading: false } );
					} )
					.catch( ( error ) => {
						this.setState( { isLoading: false, error } );
					} );
			},
			out: () => {},
		} );
	}

	/**
	 * Append to the default build options a list of GitHub releases for the plugin.
	 */
	async addGitHubReleases() {
		const releases = await getReleases();

		// Create build options from list of releases.
		const releaseOptions = releases.reduce(
			( releasesWithBuild, release ) => {
				const buildAsset = release?.assets?.find( ( asset ) => {
					return 'amp.zip' === asset.name;
				} );

				if ( ! buildAsset ) {
					return releasesWithBuild;
				}

				releasesWithBuild.push( {
					label: `${ release.name } release`,
					value: release.tag_name,
					url: buildAsset.browser_download_url,
					origin: 'release',
				} );

				return releasesWithBuild;
			},
			[]
		);

		this.setState( {
			buildOptions: this.state.buildOptions.concat( releaseOptions ),
		} );
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
	 * Installs the selected build option.
	 */
	handleInstallation() {
		const { isDevBuild, buildOption } = this.state;
		this.setState( { isInstalling: true } );

		const data = {
			id: buildOption.value,
			origin: buildOption.origin,
			isDev: isDevBuild,
		};

		if ( buildOption.url ) {
			data.url = buildOption.url;
		}

		this.adminBarSubmenuWrapper.classList.toggle( 'installing', true );

		apiFetch( {
			path: '/amp-qa-tester/v1/install',
			method: 'POST',
			data,
		} )
			.then( () => {
				window.location.reload();
			} )
			.catch( ( error ) => {
				this.adminBarSubmenuWrapper.classList.toggle( 'installing', false );
				this.adminBarSubmenuWrapper.classList.toggle( 'install-error', true );

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
				'An unknown error occurred while installing build',
				'amp-qa-tester'
			);
		}

		this.setState( {
			isInstalling: false,
			error: message,
		} );
	}

	/**
	 * Dismiss shown error.
	 */
	dismissError() {
		this.setState( { error: null } );
		this.adminBarSubmenuWrapper.classList.toggle( 'install-error', false );
	}

	render() {
		const {
			isLoading,
			isInstalling,
			error,
			buildOption,
			buildOptions,
		} = this.state;

		if ( isLoading ) {
			return (
				<>
					<div className="loading" />
					<p>{ __( 'Fetching build options…', 'amp-qa-tester' ) }</p>
				</>
			);
		}

		return (
			<>
				<BuildSelector
					buildOptions={ buildOptions }
					onOptionSelect={ this.handleChangeBuildOption }
				/>

				{ 'release' !== buildOption.origin && (
					<div className="checkbox">
						<input
							id="is-development-build"
							type="checkbox"
							checked={ this.state.isDevBuild }
							onChange={ this.handleChangeDevBuild }
						/>

						<label htmlFor="is-development-build">
							{ __( 'Development build', 'amp-qa-tester' ) }
						</label>
					</div>
				) }

				{ /*TODO: Prevent submenu from closing after clicking button to activate selection and hovering away from it */ }
				<button
					type="button"
					className="primary"
					disabled={
						buildOption.value === '' || isInstalling || error
					}
					onClick={ this.handleInstallation }
				>
					{ __( 'Install selection', 'amp-qa-tester' ) }
				</button>

				{ isInstalling && (
					<div>
						<div className="loading" />
						{ __( 'Installing build…', 'amp-qa-tester' ) }
					</div>
				) }

				{ error && (
					<div className="error">
						<span>
							{ `${ __( 'Error', 'amp-qa-tester' ) }: ${ error }` }
						</span>
						<button
							type="button"
							className="dismiss"
							onClick={ this.dismissError }
						/>
					</div>
				) }
			</>
		);
	}
}
