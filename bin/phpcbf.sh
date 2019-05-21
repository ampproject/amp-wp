#!/bin/bash
# Wrap phpcbf to turn 1 success exit code into 0 code.
# See https://github.com/squizlabs/PHP_CodeSniffer/issues/1818#issuecomment-354420927

root=$( dirname "$0" )/..

"$root/vendor/bin/phpcbf" $@
exit=$?

# Exit code 1 is used to indicate that all fixable errors were fixed correctly.
if [[ $exit == 1 ]]; then
	exit=0
fi

exit $exit
