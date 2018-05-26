# AMP Contributing Guide

Thanks for taking the time to contribute!

To start, clone this repository into your WordPress install being used for development:

```bash
cd wp-content/plugins && git clone --recursive git@github.com:Automattic/amp-wp.git amp
```

If you happened to have cloned without `--recursive` previously, please do `git submodule update --init` to ensure the [dev-lib](https://github.com/xwp/wp-dev-lib/) submodule is available for development.

Lastly, to get the plugin running in your WordPress install, run `composer install` and then activate the plugin via the WordPress dashboard or `wp plugin activate amp`.

To install the `pre-commit` hook, do `bash dev-lib/install-pre-commit-hook.sh`.

Note that pull requests will be checked against [WordPress-Coding-Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) with PHPCS, and for JavaScript linting is done with ESLint and (for now) JSCS and JSHint.

## Modifying JavaScript for Blocks

To edit JavaScript code which is built/complied, run `npm run dev` to watch the files which Webpack will build. These complied files are excluded from version control but they are included in the release packages.

## Creating a Plugin Build

To create a build of the plugin for installing in WordPress as a ZIP package, do:

```bash
git submodule update --init # (if you haven't done so yet)
composer install # (if you haven't done so yet)
npm install # (if you haven't done so yet)
npm run build
```

This will create an `amp.zip` in the plugin directory which you can install. The contents of this ZIP are also located in the `build` directory which you can `rsync` somewhere as well. 

## Updating Allowed Tags And Attributes

The file `class-amp-allowed-tags-generated.php` has the AMP specification's allowed tags and attributes. It's used in sanitization.
To update that file:
1. `cd` to the root of this plugin
2. run `bash bin/amphtml-update.sh`
That script is intended for a Linux environment like [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV).

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

## Creating a Release

Contributors who want to make a new release, follow these steps:

1. Do `npm run build` and install the `amp.zip` onto a normal WordPress install running a stable release build; do smoke test to ensure it works.
2. Bump plugin versions in `package.json` (×1), `package-lock.json` (×1, just do `npm install` first), `composer.json` (×1), and in `amp.php` (×2: the metadata block in the header and also the `AMP__VERSION` constant).
3. Add changelog entry to readme.
4. Draft blog post about the new release.
5. [Draft new release](https://github.com/Automattic/amp-wp/releases/new) on GitHub targeting the release branch, with the new plugin version as the tag and release title. Attaching the `amp.zip` build to the release. Include link to changelog in release tag.
6. Run `npm run deploy` to to commit the plugin to WordPress.org.
7. Confirm the release is available on WordPress.org; try installing it on a WordPress install and confirm it works.
8. Publish GitHub release.
9. Merge release branch into `develop`.
10. Merge release tag into `master`.
11. Publish release blog post, including link to GitHub release.
12. Close the GitHub milestone and project.
13. Make announcements.
