const core = require('@actions/core');
const { context } = require('@actions/github');
const { ReleaseDraft, makeRelease } = require('./utils');

export default function main() {
	try {
		const milestone = core.getInput('milestone', { required: true });

		// Do a sanity check on the milestone name.
		if (!/^v[0-9]+\.[0-9]+(\.[0-9]+)?$/.test(milestone)) {
			throw new Error('Milestone name is invalid.');
		}

		const tagName = milestone;
		const releaseDraft = new ReleaseDraft(milestone).generate();
		const releaseBranch = context.ref.replace('refs/heads/', '');

		const {
			data: { upload_url: assetUploadUrl }
		} = makeRelease(tagName, releaseDraft, releaseBranch);

		core.setOutput('asset_upload_url', assetUploadUrl);
	} catch (error) {
		core.setFailed(error.message);
	}
}
