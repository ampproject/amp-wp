# Draft release action

This action drafts a release for the specified milestone and release branch.

## Inputs

### `milestone`

**Required** Milestone name.

### `release_branch`

**Required** Release branch name.

## Outputs

### `asset_upload_url`

The URL for uploading assets to the release.

## Example usage

uses: actions/hello-world-javascript-action@v1.1
with:
who-to-greet: 'Mona the Octocat'
