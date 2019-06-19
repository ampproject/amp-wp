# AMP Contributing Guide

We'd love to accept your patches and contributions to this project. There are
just a few small guidelines you need to follow.

## Contributor License Agreement

Contributions to this project must be accompanied by a Contributor License
Agreement. You (or your employer) retain the copyright to your contribution;
this simply gives us permission to use and redistribute your contributions as
part of the project. Head over to <https://cla.developers.google.com/> to see
your current agreements on file or to sign a new one.

You generally only need to submit a CLA once, so if you've already submitted one
(even if it was for a different project), you probably don't need to do it
again.

## Contributors List Policy

The list of contributors who are featured on the WordPress.org plugin directory are subject to change over time. The organizations and individuals who contribute significantly and consistently (e.g. 3-month period) to the project are eligible to be listed. Those listed should generally be considered as those who take responsibility for the project (i.e. owners). Note that contributions include more than just code, though contributors who commit are [most visible](https://github.com/ampproject/amp-wp/graphs/contributors). The sort order of the contributors list should generally follow the sort order of the GitHub contributors page, though again, this order does not consider work in issues and the support forum, so it cannot be relied on solely.

## Branches

To include your changes in the next patch release (e.g. `1.0.x`), please base your branch off of the current release branch (e.g. `1.0`) and open your pull request back to that branch. If you open your pull request with the `develop` branch then it will be by default included in the next minor version (e.g. `1.x`).

## Code Reviews

All submissions, including submissions by project members, require review. We
use GitHub pull requests for this purpose. Consult
[GitHub Help](https://help.github.com/articles/about-pull-requests/) for more
information on using pull requests.

## Community Guidelines

This project follows
[Google's Open Source Community Guidelines](https://opensource.google.com/conduct/).

## Security Disclosures

The AMP Project accepts responsible security disclosures through the [Google Application Security program](https://www.google.com/about/appsecurity/).

## Code of Conduct

In addition to the Community Guidelines, this project follows
an explicit [Code of Conduct](https://github.com/ampproject/amp-wp/blob/develop/code_of_conduct.md).

## Dev Setup

To start, clone this repository into your WordPress install being used for development:

```bash
cd wp-content/plugins && git clone git@github.com:ampproject/amp-wp.git amp
```

Then install the packages:

```bash
composer install
npm install
```

And lastly, do a build of the JavaScript:

```bash
npm run build
```

Lastly, to get the plugin running in your WordPress install, run `composer install` and then activate the plugin via the WordPress dashboard or `wp plugin activate amp`.

To install the `pre-commit` hook, do `bash vendor/xwp/wp-dev-lib/scripts/install-pre-commit-hook.sh`.

Note that pull requests will be checked against [WordPress-Coding-Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) with PHPCS, and for JavaScript linting is done with ESLint and (for now) JSCS and JSHint.

## Modifying JavaScript for Blocks

To edit JavaScript code which is built/complied, run `npm run dev` to watch the files which Webpack will build. These complied files are excluded from version control but they are included in the release packages.

## Creating a Plugin Build

To create a build of the plugin for installing in WordPress as a ZIP package, do:

```bash
composer install # (if you haven't done so yet)
npm install # (if you haven't done so yet)
npm run build
```

This will create an `amp.zip` in the plugin directory which you can install. The contents of this ZIP are also located in the `build` directory which you can `rsync` somewhere as well.

## Updating Allowed Tags And Attributes

The file `class-amp-allowed-tags-generated.php` has the AMP specification's allowed tags and attributes. It's used in sanitization.

To update that file, perform the following steps:

1. `cd` to the root of this plugin
2. Run `./bin/amphtml-update.sh` (or `lando ssh -c './bin/amphtml-update.sh'` if using Lando).
3. Review the diff.
4. Update tests based on changes to the spec.
5. Commit changes.

This script is intended for a Linux environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV) or [Lando wordpressdev](https://github.com/felixarntz/wordpressdev).

## Testing Media And Embed Support

The following script creates a post in order to test support for WordPress media and embeds.
To run it:
1. `ssh` into an environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)
2. `cd` to the root of this plugin
3. run `wp eval-file bin/create-embed-test-post.php`
4. go to the URL that is output in the command line

## Testing Widgets Support

The following script adds an instance of every default WordPress widget to the first registered sidebar.
To run it:
1. `ssh` into an environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)
2. `cd` to the root of this plugin
3. run `wp eval-file bin/add-test-widgets-to-sidebar.php`
4. There will be a message indicating which sidebar has the widgets. Please visit a page with that sidebar.

## Testing Comments Support

The following script creates a post with comments in order to test support for WordPress comments.
To run it:
1. `ssh` into an environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)
2. `cd` to the root of this plugin
3. run `wp eval-file bin/create-comments-on-test-post.php`
4. go to the URL that is output in the command line

## Testing Gutenberg Block Support

The following script creates a post with all core Gutenberg blocks. To run it:
1. `ssh` into an environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)
2. `cd` to the root of this plugin
3. run `bash bin/create-gutenberge-test-post.sh`
4. go to the URL that is output in the command line

## PHPUnit Testing

Please run these tests in an environment with WordPress unit tests installed, like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV).

Run tests:

``` bash
$ phpunit
```

Run tests with an HTML coverage report:

``` bash
$ phpunit --coverage-html /tmp/report
```

When you push a commit to your PR, Travis CI will run the PHPUnit tests and sniffs against the WordPress Coding Standards.

## Creating a Prerelease

1. Create changelog draft on [Wiki page](https://github.com/ampproject/amp-wp/wiki/Release-Changelog-Draft).
1. Check out the branch intended for release (`develop` for major, `x.y` for minor) and pull latest commits.
1. Do `npm install && composer selfupdate && composer install`.
1. Do `npm run build` and install the `amp.zip` onto a normal WordPress install running a stable release build; do smoke test to ensure it works.
1. Bump plugin versions in `amp.php` (×2: the metadata block in the header and also the `AMP__VERSION` constant).
1. [Draft new release](https://github.com/ampproject/amp-wp/releases/new) on GitHub targeting the required branch (`develop` for major, `x.y` for minor).
    1. Use the new plugin version as the tag (e.g. `1.2-beta3` or `1.2.1-RC1`)
    1. USe new version as the title, followed by some highlight tagline of the release.
    1. Attach the `amp.zip` build to the release.
    1. Add changelog entry to the release, link to compare view comparing previous release, and link to milestone.
    1. Make sure “Pre-release” is checked.
1. Publish GitHub release.
1. Create built release tag (from the just-created `build` directory):
    1. do `git fetch --tags && ./bin/tag-built.sh`
    1. Add link from release notes.
1. Make announcements on Twitter and the #amp-wp channel on AMP Slack, linking to release post or GitHub release.
1. Bump version in release branch, e.g. `…-alpha` to `…-beta1` and `…-beta2` to `…-RC1`

## Creating a Stable Release

Contributors who want to make a new release, follow these steps:

1. Create changelog draft on [Wiki page](https://github.com/ampproject/amp-wp/wiki/Release-Changelog-Draft).
    1. Gather props list of the entire release, including contributors of code, design, testing, project management, etc.
1. Update readme including the description, contributors, and screenshots.
1. Draft release post.
1. For major release, draft blog post about the new release.
1. For minor releases, make sure all merged commits in `develop` have been also merged onto release branch.
1. Check out the branch intended for release (`develop` for major, `x.y` for minor) and pull latest commits.
1. Do `npm install && composer selfupdate && composer install`.
1. Bump plugin versions in `amp.php` (×2: the metadata block in the header and also the `AMP__VERSION` constant). Verify via `npx grunt shell:verify_matching_versions`. Ensure patch version number is supplied for major releases, so `1.2-RC1` should bump to `1.2.0`.
1. Do `npm run build` and install the `amp.zip` onto a normal WordPress install running a stable release build; do smoke test to ensure it works.
1. Optionally do sanity check by comparing the `build` directory with the previously-deployed plugin on WordPress.org for example: `svn export https://plugins.svn.wordpress.org/amp/trunk /tmp/amp-trunk; diff /tmp/amp-trunk/ ./build/` (instead of straight `diff`, it's best to use a GUI like `idea diff`, `phpstorm diff`, or `opendiff`).
1. [Draft new release](https://github.com/ampproject/amp-wp/releases/new) on GitHub targeting the required branch (`develop` for major, `x.y` for minor):
    1. Use the new plugin version as the tag (e.g. `1.2.0` or `1.2.1`)
    1. Attach the `amp.zip` build to the release.
    1. Add changelog entry to the release, link to compare view comparing previous release, and link to milestone.
1. Run `npm run deploy` to commit the plugin to WordPress.org.
1. Confirm the release is available on WordPress.org; try installing it on a WordPress install and confirm it works.
1. Publish GitHub release.
1. Create built release tag (from the just-created `build` directory):
    1. do `git fetch --tags && ./bin/tag-built.sh`
    1. Add link from release notes.
1. For new major releases, create a release branch from the tag. Patch versions are made from the release branch.
1. For minor releases, bump `Stable tag` in the `readme.txt`/`readme.md` in `develop`. Cherry-pick other changes as necessary.
1. Merge release tag into `master`.
1. Close the GitHub milestone (and project).
1. Publish release blog post (if applicable), including link to GitHub release.
1. Make announcements on Twitter and the #amp-wp channel on AMP Slack, linking to release post or GitHub release.
1. Bump version in release branch. After major release (e.g. `1.2.0`), bump to `1.3.0-alpha` on `develop`; after minor release (e.g. `1.2.1`) bump version to `1.2.2-alpha` on the release branch.
