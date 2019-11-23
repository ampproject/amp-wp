/**
 * External dependencies
 */
import path from 'path';
import { getInput, info, setFailed, setOutput } from '@actions/core';
import { GitHub, context } from '@actions/github';

/**
 * Internal dependencies
 */
import GitHubClient from './githubClient.js';
import { getBranchName, getCanonicalTag, getPluginVersion } from './utils.js';

( async () => {
	const repoToken = getInput( 'repo-token', { required: true } );
	const zipPath = getInput( 'zip', { required: true } );
	const zipName = path.basename( zipPath );

	const { owner, repo } = context.repo;
	const client = new GitHubClient( new GitHub( repoToken ), owner, repo );

	const branch = getBranchName( context.ref );
	const tag = getCanonicalTag( branch );
	const pluginVersion = getPluginVersion();

	info( `Plugin version: ${ pluginVersion }` );
	info( `Fetching release details for the ${ branch } branch` );

	const releaseDesc = `Build for ${ pluginVersion }.`;
	let uploadUrl;

	try {
		// An error will be thrown if a release for the tag was not found.
		const currentRelease = await client.getReleaseByTag( tag );

		info( `Updating release description for '${ tag }'` );
		const release = await client.updateRelease( currentRelease.data.id, tag, releaseDesc );
		uploadUrl = release.data.upload_url;

		for ( const asset of release.data.assets ) {
			if ( zipName === asset.name ) {
				info( `Deleting ${ zipName } asset` );
				// eslint-disable-next-line no-await-in-loop
				await client.deleteReleaseAsset( asset.id );
				break;
			}
		}
	} catch ( error ) {
		info( `Creating a release for '${ tag }'` );
		const release = await client.createRelease( tag, tag, releaseDesc );
		uploadUrl = release.data.upload_url;
	}

	info( `Uploading ${ zipName }` );
	await client.uploadReleaseAsset( uploadUrl, zipName, zipPath );

	setOutput( 'branch', branch );
} )().catch( ( error ) => {
	setFailed( error.message );
} );
