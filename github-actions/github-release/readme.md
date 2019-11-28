# GitHub release action

This action creates a zip of the built plugin and uploads it to the release for the branch that
triggered the action.

## Building the action for distribution

**Note:** GitHub executes actions as a complete package of code before it can be used in workflows.
Because of this, any package dependencies required to run the JavaScript code must be committed
(including the `node_modules` folder). An alternative to this is to compile the code and modules into one file using
[zeit/ncc](https://github.com/zeit/ncc) (which this action utilizes).

Once any change has been made to the JavaScript code or any files it requires:

- Compile the code. `ncc build src/index.js`
- Ensure all files in the `dist` folder are committed

## Action inputs

### `repo-token`

**Required** GitHub secret token.

### `build-path`

**Required** Path to folder of built plugin.

## Action outputs

### `branch`

The git tag this action received.

## Example usage

```
uses: ./github-actions/github-release
with:
  repo-token: ${{ secrets.GITHUB_TOKEN }}
  build-path: build # relative path to build folder
```
