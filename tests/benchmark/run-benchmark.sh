#!/bin/bash +x
#
#   This file is part of the AMP Plugin for WordPress.
#
#   The AMP Plugin for WordPress is free software: you can redistribute
#   it and/or modify it under the terms of the GNU General Public
#   License as published by the Free Software Foundation, either version 2
#   of the License, or (at your option) any later version.
#
#   The AMP Plugin for WordPress is distributed in the hope that it will
#   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with the AMP Plugin for WordPress. If not, see <https://www.gnu.org/licenses/>.
#

pushd `dirname $0` > /dev/null
BENCH_DIR=`pwd`
popd > /dev/null

declare -a urls=(
	"http://amp.dev/2013/01/10/image-alignment/amp/?XDEBUG_PROFILE=1",
	"http://amp.dev/2016/12/28/everything-amp/amp/?XDEBUG_PROFILE=1"
)

NUM_TIMES_TO_LOAD_EACH_URL=100

sudo rm /tmp/*_srv_www_amp_htdocs_index_php*

## now loop through the above array
for url in "${urls[@]}"
do
	printf 'Loading %s \n' $url
    for i in `seq 1 $NUM_TIMES_TO_LOAD_EACH_URL`;
    do
        curl -s -o /dev/null "$url"
        printf '.'
        sleep 1
    done
    printf '\n'
done

combined_file="cachegrind.out.combined-$(date +%s)"

cat /tmp/*_srv_www_amp_htdocs_index_php* > "/tmp/$combined_file"

echo "Wrote $combined_file"
