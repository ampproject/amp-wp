/**
 * External dependencies
 */
import { getInput, info, setFailed, setOutput } from '@actions/core';
import { GitHub, context } from '@actions/github';

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

const main = async () => {
	const repoToken = getInput( 'repo-token', { required: true } );
	const zipPath = getInput( 'zip', { required: true } );
	const client = new GitHub( repoToken );

	const branch = getBranchName( context.ref );
	const tag = getCanonicalTag( branch );

	info( `Fetching release details for the ${ branch } branch` );

	const { owner, repo } = context.payload;
	let releaseId, releaseFunc;

	try {
		// An error will be thrown if a release for the tag was not found.
		const release = await client.repos.getReleaseByTag( { owner, repo, tag } );
		releaseId = release.data.id;
		releaseFunc = updateRelease;

		info( `Updating release description for '${ tag }'` );
	} catch ( error ) {
		releaseId = tag;
		releaseFunc = createRelease;

		info( `Creating a release for '${ tag }'` );
	}

	const releaseName = tag;
	const releaseDesc = `Build for ${ getPluginVersion() }.`;
	const release = releaseFunc( client, releaseId, owner, repo, releaseName, releaseDesc );

	const uploadUrl = release.data.upload_url;

	info( 'Uploading assets' );

	await uploadReleaseAsset( client, uploadUrl, 'amp.zip', zipPath );

	setOutput( 'branch', branch );
};

try {
	main();
} catch ( error ) {
	setFailed( error.message );
}
