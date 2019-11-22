/**
 * External dependencies
 */
import core from '@actions/core';
import github from '@actions/github';

/**
 * Internal dependencies
 */
import {
	createRelease,
	getBranchName,
	getCanonicalTag,
	getPluginVersion,
	updateRelease,
	uploadReleaseAsset,
} from './utils';

( async () => {
	try {
		const repoToken = core.getInput( 'repo-token', { required: true } );
		const zipPath = core.getInput( 'zip', { required: true } );
		const client = new github.GitHub( repoToken );

		const branch = getBranchName( github.context.ref );
		const tag = getCanonicalTag( branch );

		core.info( `Fetching release details for the ${ branch } branch` );

		const { owner, repo } = github.context.payload;
		let releaseId, releaseFunc;

		try {
			// An error will be thrown if a release for the tag was not found.
			const release = await client.repos.getReleaseByTag( { owner, repo, tag } );
			releaseId = release.data.id;
			releaseFunc = updateRelease;

			core.info( `Updating release description for '${ tag }'` );
		} catch ( error ) {
			releaseId = tag;
			releaseFunc = createRelease;

			core.info( `Creating a release for '${ tag }'` );
		}

		const releaseName = tag;
		const releaseDesc = `Build for ${ getPluginVersion() }.`;
		const release = releaseFunc( client, releaseId, owner, repo, releaseName, releaseDesc );

		const uploadUrl = release.data.upload_url;

		core.info( 'Uploading assets' );

		await uploadReleaseAsset( client, uploadUrl, 'amp.zip', zipPath );

		core.setOutput( 'branch', branch );
	} catch ( error ) {
		core.setFailed( error.message );
	}
} )();
