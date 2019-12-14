#!/bin/bash +x

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
