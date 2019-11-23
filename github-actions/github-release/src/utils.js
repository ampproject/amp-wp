/**
 * External dependencies
 */
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';

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

export const getPluginVersion = ( buildPath ) => {
	const versionRegex = /\* Version: (\d+\.\d+\.\d+(?:-.+)?)/;
	const pluginFilePath = `${ buildPath }/amp.php`;
	const pluginFileContent = fs.readFileSync( pluginFilePath );

	return versionRegex.exec( pluginFileContent )[ 1 ];
};

export const createZipFile = ( zipName, buildPath ) => {
	execSync( `cd ${ buildPath } && zip -r ../${ zipName } .` );

	return `${ path.dirname( buildPath ) }/${ zipName }`;
};
