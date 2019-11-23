/**
 * External dependencies
 */
import path from 'path';
import fs from 'fs';

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
	const pluginFilePath = path.resolve( process.env.GITHUB_WORKSPACE, 'amp.php' );
	const pluginFileContent = fs.readFileSync( pluginFilePath );

	return versionRegex.exec( pluginFileContent )[ 1 ];
};
