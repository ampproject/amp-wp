const { context } = require('@actions/github');
const github = require('../github');

const makeRelease = async (tagName, body, commitish) => {
	const { owner, repo } = context.repo;

	const {
		data: { id: releaseId }
	} = await github.repos.getReleaseByTag({
		owner,
		repo,
		tag: tagName
	});

	let releaseResponse;

	if (!releaseId) {
		releaseResponse = await github.repos.createRelease({
			owner,
			repo,
			tag_name: tagName,
			name: tagName,
			body,
			draft: true,
			prerelease: false,
			target_commitish: commitish
		});
	} else {
		releaseResponse = await github.repos.updateRelease({
			owner,
			repo,
			body
		});
	}

	return releaseResponse;
};

export default makeRelease;
