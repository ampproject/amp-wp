#!/bin/bash
set -e
# Travis CI Deploy script to Pantheon

# Positional args:
pantheon_site=$1
pantheon_uuid=$2
ssh_identity=$3
pantheon_branch=$4

if [[ -z "$pantheon_site" ]]; then
    echo "Missing pantheon_site positional arg 1"
    exit 1
fi
if [[ -z "$pantheon_uuid" ]]; then
    echo "Missing pantheon_uuid positional arg 2"
    exit 1
fi
if [[ -z "$ssh_identity" ]]; then
    echo "Missing ssh_identity positional arg 3"
    exit 1
fi
if [[ -z "$pantheon_branch" ]]; then
    echo "Missing pantheon_branch positional arg 4"
    exit 1
fi

cd "$(dirname "$0")/.."
project_dir="$(pwd)"
repo_dir="$HOME/deployment-targets/$pantheon_site"

# Restrict deploys to commits pushed to a branch name that can be used as a subdomain, specifically here on a Pantheon multidev environment:
# "Branch names can contain any ASCII letter and number (a through z, 0 through 9) and hyphen (dash). The branch name must start with a letter or number.
# Currently, the maximum length is 11 characters and environments cannot be created with the following reserved names."
# Note: master is allowed since it maps to dev; the dev branch is instead disallowed.
if ! [[ $pantheon_branch =~ ^[a-z][a-z0-9-]{0,10}$ ]] || [[ $pantheon_branch =~ ^(live|test|dev|settings|team|support|debug|multidev|files|tags|billing)$ ]]; then
    echo "Aborting since branch '$pantheon_branch' cannot be an environment."
    exit 1
fi

ssh-add $ssh_identity

if ! grep -q "codeserver.dev.$pantheon_uuid.drush.in" ~/.ssh/known_hosts; then
    ssh-keyscan -p 2222 codeserver.dev.$pantheon_uuid.drush.in >> ~/.ssh/known_hosts
fi

if ! grep -q "codeserver.dev.$pantheon_uuid.drush.in" ~/.ssh/config; then
    echo "Host $pantheon_site" >> ~/.ssh/config
    echo "  Hostname codeserver.dev.$pantheon_uuid.drush.in" >> ~/.ssh/config
    echo "  User codeserver.dev.$pantheon_uuid" >> ~/.ssh/config
    echo "  IdentityFile $ssh_identity" >> ~/.ssh/config
    echo "  IdentitiesOnly yes" >> ~/.ssh/config
    echo "  Port 2222" >> ~/.ssh/config
    echo "  KbdInteractiveAuthentication no" >> ~/.ssh/config
fi
git config --global user.name "Travis CI"
git config --global user.email "travis-ci+$pantheon_site@xwp.co"

# Set the branch.
if [[ $pantheon_branch == 'master' ]]; then
    pantheon_env=dev
else
    pantheon_env=$pantheon_branch
fi

if [ ! -e "$repo_dir/.git" ]; then
    git clone -v ssh://codeserver.dev.$pantheon_uuid@codeserver.dev.$pantheon_uuid.drush.in:2222/~/repository.git "$repo_dir"
fi

cd "$repo_dir"
git fetch

if git rev-parse --verify --quiet "$pantheon_branch" > /dev/null; then
    git checkout "$pantheon_branch"
else
    git checkout -b "$pantheon_branch"
fi
if git rev-parse --verify --quiet "origin/$pantheon_branch" > /dev/null; then
    git reset --hard "origin/$pantheon_branch"
fi

# Install and build.
cd "$project_dir"
if [ ! -e node_modules/.bin ]; then
    npm install
fi
PATH="node_modules/.bin/:$PATH"
npm run build
rsync -avz --delete ./build/ "$repo_dir/wp-content/plugins/amp/"
git --no-pager log -1 --format="Build AMP plugin at %h: %s" > /tmp/commit-message.txt

# Commit and deploy.
cd "$repo_dir"
git add -A "wp-content/plugins/amp/"
git commit -F /tmp/commit-message.txt
git push origin $pantheon_branch

echo "View site at http://$pantheon_env-$pantheon_site.pantheonsite.io/"
echo "Access Pantheon dashboard at https://dashboard.pantheon.io/sites/$pantheon_uuid#$pantheon_env"
