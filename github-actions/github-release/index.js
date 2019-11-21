/**
 * Node dependencies
 */
const fs = require( 'fs' );

/**
 * GitHub dependencies
 */
const core = require( '@actions/core' );
const github = require( '@actions/github' );

( async () => {
	try {
		const repoToken = core.getInput( 'repo-token', { required: true } );
		const zipPath = core.getInput( 'zip', { required: true } );

		const client = new github.GitHub( repoToken );

		const gitRef = github.context.ref;
		let gitTag = gitRef.match( /refs\/tags\/(.*)/ );

		if ( null === gitTag ) {
			throw new Error( `Failed to determine tag from git ref: ${ gitRef }` );
		}

		gitTag = gitTag[ 1 ];

		core.info( `Fetching release details for git tag: ${ gitTag }` );

		const { owner, repo } = github.context.payload;

		const releaseInfo = {};

		try {
			// An error will be thrown if a release for the tag was not found.
			const currentReleaseInfo = await client.repos.getReleaseByTag( { owner, repo, tag: gitTag } );

			core.info( 'Tag was previously released, deleting it' );

			releaseInfo.tag_name = currentReleaseInfo.data.tag_name;
			releaseInfo.name = currentReleaseInfo.data.name;
			releaseInfo.body = currentReleaseInfo.data.body;
			await client.repos.deleteRelease( { owner, repo, release_id: currentReleaseInfo.data.id } );
		} catch ( error ) {
			core.info( 'Release not found for tag' );

			releaseInfo.tag_name = gitTag;
			releaseInfo.name = gitTag;
			releaseInfo.body = `Build for ${ gitTag }`;
		}

		core.info( 'Creating new release' );

		const newReleaseInfo = await client.repos.createRelease( { owner, repo, ...releaseInfo } );

		core.info( 'Uploading assets' );

		const fileName = 'amp.zip';

		await client.repos.uploadReleaseAsset( {
			url: newReleaseInfo.data.upload_url,
			headers: {
				'Content-Length': fs.lstatSync( zipPath ).size,
				'Content-Type': 'application/zip',
			},
			name: fileName,
			file: fs.readFileSync( zipPath ),
		} );

		core.setOutput( 'git-tag', gitTag );
	} catch ( error ) {
		core.setFailed( error.message );
	}
} )();
