/**
 * External dependencies
 */
import fs from 'fs';
import path from 'path';

export const getBranchName = ( ref ) => {
	const branch = ref.match( /refs\/heads\/(.*)/ );

	if ( null === branch ) {
		throw new Error( `Failed to determine branch from git ref: ${ ref }` );
	}

	return branch[ 1 ];
};

export const getCanonicalTag = ( branchName ) => {
	return 'develop' === branchName ? 'nightly' : `${ branchName }-nightly`;
};

export const getPluginVersion = () => {
	const versionRegex = /\* Version: (\d+\.\d+\.\d+(?:-\w+)?)/;
	const pluginFilePath = path.resolve( process.cwd(), '../../amp.php' );
	const pluginFileContent = fs.readFileSync( pluginFilePath );

	return versionRegex.exec( pluginFileContent )[ 1 ];
};

export const createRelease = async ( githubClient, tagName, owner, repo, name, body, isPreRelease = true ) => {
	await githubClient.repos.createRelease( {
		owner,
		repo,
		tag_name: tagName,
		name,
		body,
		prerelease: isPreRelease,
	} );
};

export const updateRelease = async ( githubClient, releaseId, owner, repo, name, body, isPreRelease = true ) => {
	await githubClient.repos.updateRelease( {
		owner,
		repo,
		release_id: releaseId,
		name,
		body,
		prerelease: isPreRelease,
	} );
};

export const uploadReleaseAsset = async ( githubClient, uploadUrl, fileName, filePath ) => {
	await githubClient.repos.uploadReleaseAsset( {
		url: uploadUrl,
		headers: {
			'Content-Length': fs.lstatSync( filePath ).size,
			'Content-Type': 'application/zip',
		},
		name: fileName,
		file: fs.readFileSync( filePath ),
	} );
};
