/**
 * External dependencies
 */
import fs from 'fs';

class GitHubClient {
	constructor( client, owner, repo ) {
		this.client = client;
		this.owner = owner;
		this.repo = repo;
	}

	getReleaseByTag( tag ) {
		return this.client.repos.getReleaseByTag( {
			owner: this.owner,
			repo: this.repo,
			tag,
		} );
	}

	createRelease( tagName, name, body, isPreRelease = true ) {
		return this.client.repos.createRelease( {
			owner: this.owner,
			repo: this.repo,
			tag_name: tagName,
			name,
			body,
			prerelease: isPreRelease,
		} );
	}

	updateRelease( releaseId, name, body, isPreRelease = true ) {
		return this.client.repos.updateRelease( {
			owner: this.owner,
			repo: this.repo,
			release_id: releaseId,
			name,
			body,
			prerelease: isPreRelease,
		} );
	}

	uploadReleaseAsset( uploadUrl, fileName, filePath ) {
		return this.client.repos.uploadReleaseAsset( {
			url: uploadUrl,
			headers: {
				'Content-Length': fs.lstatSync( filePath ).size,
				'Content-Type': 'application/zip',
			},
			name: fileName,
			file: fs.readFileSync( filePath ),
		} );
	}

	deleteReleaseAsset( assetId ) {
		return this.client.repos.deleteReleaseAsset( {
			owner: this.owner,
			repo: this.repo,
			asset_id: assetId,
		} );
	}
}

export default GitHubClient;
