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

## Getting Started

### Requirements

To contribute to this plugin, you need the following tools installed on your computer:

* [Composer](https://getcomposer.org/) - to install PHP dependencies.
* [Node.js](https://nodejs.org/en/) - to install JavaScript dependencies.
* [WordPress](https://wordpress.org/download/) - to run the actual plugin.

You should be running a Node version matching the [current active LTS release](https://github.com/nodejs/Release#release-schedule) or newer for this plugin to work correctly. You can check your Node.js version by typing node -v in the Terminal prompt.

If you have an incompatible version of Node in your development environment, you can use [nvm](https://github.com/creationix/nvm) to change node versions on the command line:

```bash
nvm install
```

## Local Environment

Since you need a WordPress environment to run the plugin in, the quickest way to get up and running is to use the provided Docker setup. Install [Docker](https://www.docker.com/products/docker-desktop) and [Docker Compose](https://docs.docker.com/compose/install/) by following the instructions on their website.

You can then clone this project somewhere on your computer:

```bash
git clone git@github.com:ampproject/amp-wp.git amp
cd amp
```

After that, run a script to set up the local environment. It will automatically verify whether Docker, Composer and Node.js are configured properly and start the local WordPress instance. You may need to run this script multiple times if prompted.

```bash
./bin/local-env/start.sh
```

If everything was successful, you'll see this on your screen:

```
Welcome to...

MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
MMMMMMMMMMMMMMMMWNXK0OOkkkkOO0KXNWMMMMMMMMMMMMMMMM
MMMMMMMMMMMMWX0kdlc:::::::::::ccodk0NWMMMMMMMMMMMM
MMMMMMMMMWXOdl::::::::::::::::::::::lx0NMMMMMMMMMM
MMMMMMMWKxl::::::::::::::::::::::::::::oOXWMMMMMMM
MMMMMMXkl:::::::::::::::::col::::::::::::oONMMMMMM
MMMMW0o:::::::::::::::::ox0Xk:::::::::::::cxXWMMMM
MMMW0l:::::::::::::::::dKWWXd:::::::::::::::dXMMMM
MMW0l::::::::::::::::cxXWMM0l::::::::::::::::dXMMM
MMXd::::::::::::::::ckNMMMWkc::::::::::::::::ckWMM
MWOc:::::::::::::::lONMMMMNkooool:::::::::::::oXMM
MWk:::::::::::::::l0WMMMMMMWNWNNOc::::::::::::l0MM
MNx::::::::::::::oKWMMMMMMMMMMW0l:::::::::::::cOWM
MNx:::::::::::::oKWWWMMMMMMMMNOl::::::::::::::c0MM
MWOc::::::::::::cddddxKWMMMMNkc:::::::::::::::oKMM
MMXd:::::::::::::::::l0MMMMXdc:::::::::::::::ckWMM
MMW0l::::::::::::::::dXMWWKd:::::::::::::::::oXMMM
MMMWOl:::::::::::::::kWW0xo:::::::::::::::::oKWMMM
MMMMW0l:::::::::::::l0NOl::::::::::::::::::dKWMMMM
MMMMMWKdc:::::::::::cooc:::::::::::::::::lkNMMMMMM
MMMMMMMN0dc::::::::::::::::::::::::::::lxKWMMMMMMM
MMMMMMMMMWKxoc::::::::::::::::::::::coOXWMMMMMMMMM
MMMMMMMMMMMWNKkdoc:::::::::::::cloxOKWMMMMMMMMMMMM
MMMMMMMMMMMMMMMWNX0OkkxxxxxxkO0KXWWMMMMMMMMMMMMMMM
MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
```

The WordPress installation should be available at http://localhost:8890 (**Username**: admin, **Password**: password).

To later turn off the local environment, you can run:

```bash
npm run env:stop
```

To bring it back later, run:

```bash
npm run env:start
```

Also, if you need to reset the local environment's database, you can run:

```bash
npm run env:reset-site
```

### Custom Environment

Alternatively, you can use your own local WordPress environment and clone this repository right into your `wp-content/plugins` directory.

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
npm run build:js
```

Lastly, to get the plugin running in your WordPress install, run `composer install` and then activate the plugin via the WordPress dashboard or `wp plugin activate amp`.

## Developing the Plugin

Whether you use the pre-existing local environment or a custom one, any PHP code changes will be directly visible during development.

However, for JavaScript this involves a build process. To watch for any JavaScript file changes and re-build it when needed, you can run the following command:

```bash
npm run dev
```

This way you will get a development build of the JavaScript, which makes debugging easier.

To get a production build, run:

```bash
npm run build:js
```

### Creating a Plugin Build

To create a build of the plugin for installing in WordPress as a ZIP package, run:

```bash
npm run build
```

This will create an `amp.zip` in the plugin directory which you can install. The contents of this ZIP are also located in the `build` directory which you can `rsync` somewhere as well if needed.

### Writing Tests

#### PHP Unit Tests

The AMP plugin uses the [PHPUnit](https://phpunit.de/) testing framework to write unit and integration tests for the PHP part.

To run the full test suite, you can use the following command:

```bash
npm run test:php
```

You can also just run test for a specific function or class by using something like this:

```bash
npm run test:php -- --filter=AMP_Theme_Support
```

See `npm run test:php:help` to see all the possible options.

#### JavaScript Unit Tests

[Jest](https://jestjs.io/) is used as the JavaScript unit testing framework.

To run the full test suite, you can use the following command:

```bash
npm run test:js
```

You can also watch for any file changes and only run tests that failed or have been modified:

```bash
npm run test:js:watch
```

See `npm run test:js:help` to get a list of additional options that can be passed to the test runner. 

#### End-to-End Tests

This project leverages the local Docker-based environment to facilitate end-to-end (e2e) testing using Puppeteer.

To run the full test suite, you can use the following command:

```bash
npm run test:e2e
```

You can also watch for any file changes and only run tests that failed or have been modified:

```bash
npm run test:e2e:watch
```

Not using the built-in local environment? You can also pass any other URL to run the tests against. Example:

```bash
npm run test:e2e -- --wordpress-base-url=https://my-amp-dev-site.local
```

For debugging purposes, you can also run the E2E tests in non-headless mode:

```bash
npm run test:e2e:interactive
```

Note that this will also slow down all interactions during tests by 80ms. You can control these values individually too:

```bash
PUPPETEER_HEADLESS=false npm run test:e2e # Interactive mode, normal speed.
PUPPETEER_SLOWMO=200 npm run test:e2e # Headless mode, slowed down by 200ms.
```

Sometimes one might want to test additional scenarios that aren't possible in a WordPress installation out of the box. That's why the test setup allows for for adding some utility plugins that can be activated during E2E tests.
                                                                                                                                                       
For example, such a plugin could create a custom post type and the accompanying E2E test would verify that block validation errors are shown for this custom post type too.

These plugins can be added to `tests/e2e/plugins` and then activated via the WordPress admin.

### Coding Standards

All contributions to this project will be checked against [WordPress-Coding-Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) with PHPCS, and for JavaScript linting is done with ESLint.

To verify your code meets the requirements, you can run `npm run lint`.

You can also install a `pre-commit` hook by running `bash vendor/xwp/wp-dev-lib/scripts/install-pre-commit-hook.sh`. This way, your code will be checked automatically before committing any changes.

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

## Creating a Prerelease

1. Create changelog draft on [Wiki page](https://github.com/ampproject/amp-wp/wiki/Release-Changelog-Draft).
1. Check out the branch intended for release (`develop` for major, `x.y` for minor) and pull latest commits.
1. Bump plugin versions in `amp.php` (×2: the metadata block in the header and also the `AMP__VERSION` constant).
1. Do `npm install && composer selfupdate && composer install`.
1. Do `npm run build` and install the `amp.zip` onto a normal WordPress install running a stable release build; do smoke test to ensure it works.
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
