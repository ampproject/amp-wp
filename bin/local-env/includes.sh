#!/bin/bash

# Common variables.
DOCKER_COMPOSE_FILE_OPTIONS="-f $(dirname "$0")/docker-compose.yml"
# These are the containers and values for the development site.
CLI='cli'
CONTAINER='wordpress'
DATABASE='mysql'
SITE_TITLE='AMP Dev'

##
# Download from a remote source.
#
# Checks for the existence of curl and wget, then downloads the remote file using the first available option.
#
# @param {string} remote  The remote file to download.
# @param {string} [local] Optional. The local filename to use. If it isn't passed, STDOUT is used.
#
# @return {bool} Whether the download succeeded or not.
##
download() {
    if command_exists "curl"; then
        curl -s -o "${2:--}" "$1"
    elif command_exists "wget"; then
		wget -nv -O "${2:--}" "$1"
    fi
}

##
# Add error message formatting to a string, and echo it.
#
# @param {string} message The string to add formatting to.
##
error_message() {
	echo -en "\033[31mERROR\033[0m: $1"
}

##
# Add warning message formatting to a string, and echo it.
#
# @param {string} message The string to add formatting to.
##
warning_message() {
	echo -en "\033[33mWARNING\033[0m: $1"
}

##
# Add status message formatting to a string, and echo it.
#
# @param {string} message The string to add formatting to.
##
status_message() {
	echo -en "\033[32mSTATUS\033[0m: $1"
}

##
# Add formatting to an action string.
#
# @param {string} message The string to add formatting to.
##
action_format() {
	echo -en "\033[32m$1\033[0m"
}

##
# Check if the command exists as some sort of executable.
#
# The executable form of the command could be an alias, function, builtin, executable file or shell keyword.
#
# @param {string} command The command to check.
#
# @return {bool} Whether the command exists or not.
##
command_exists() {
	type -t "$1" >/dev/null 2>&1
}

##
# Docker Compose helper
#
# Calls docker-compose with common options.
##
dc() {
	docker compose $DOCKER_COMPOSE_FILE_OPTIONS "$@"
}

##
# WP CLI
#
# Executes a WP CLI request in the CLI container.
##
wp() {
	dc exec -T -u www-data $CONTAINER wp "$@"
}

##
# MySQL CLI.
#
# Executes the given MySQL client command in the database container.
##
mysql() {
	dc exec -T -e MYSQL_PWD=example $DATABASE mysql "$@"
}

##
# WordPress Container helper.
#
# Executes the given command in the wordpress container.
##
container() {
	dc exec -T $CONTAINER "$@"
}

##
# Download specific version of Gutenberg plugin.
#
# @param {string} version The version of Gutenberg to download.
##
download_gutenberg() {
	local version="$1"
	# by default save as gutenberg.$version.zip
	local download_path="${2:-gutenberg.${version}.zip}"
	local url="https://downloads.wordpress.org/plugin/gutenberg.${version}.zip"

	echo -e $(status_message "Downloading Gutenberg ${version} from ${url}...")
	download "$url" "$download_path"
	echo -e $(status_message "Downloaded Gutenberg ${version} to ${download_path}")
}
