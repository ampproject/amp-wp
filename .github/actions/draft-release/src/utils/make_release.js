const core = require('@actions/core');
const github = require('@actions/github');
const { RequestError } = require('@octokit/request-error');

const makeRelease = async (tagName, body, commitish) => {
	const { owner, repo } = github.context.repo;
	const octokit = github.getOctokit(process.env.GITHUB_TOKEN);

	let release;
	try {
		release = await octokit.rest.repos.getReleaseByTag({
			owner,
			repo,
			tag: tagName
		});
	} catch (error) {
		if (error instanceof RequestError && error.status === 404) {
			release = null;
		} else {
			throw error;
		}
	}

	let releaseResponse;

	if (!release) {
		releaseResponse = await octokit.rest.repos.createRelease({
			owner,
			repo,
			tag_name: tagName,
			name: tagName,
			body,
			draft: true,
			prerelease: false,
			target_commitish: commitish
		});
		core.info(`Successfully drafted a release for ${tagName}`);
	} else {
		if (release.draft !== true) {
			throw new Error(`The ${tagName} release is not a draft. Aborting...`)
		}

		releaseResponse = await octokit.rest.repos.updateRelease({
			owner,
			repo,
			release_id: release.id,
			tag_name: tagName,
			name: tagName,
			body
		});
		core.info(`Successfully updated the ${tagName} draft release`);
	}

	return releaseResponse;
};

module.exports = makeRelease;
