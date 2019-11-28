# GitHub release action

This action creates a zip of the built plugin and uploads it to the release for the branch that
triggered the action.

## Inputs

### `repo-token`

**Required** GitHub secret token.

### `build-path`

**Required** Path to folder of built plugin.

## Outputs

### `branch`

The git tag this action received.

## Example usage

```
uses: ./github-actions/github-release
with:
  repo-token: ${{ secrets.GITHUB_TOKEN }}
  build-path: build # relative path to build folder
```
