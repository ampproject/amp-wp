#!/bin/bash

REGEX=$1
CHANGED_FILES=$(git diff develop --diff-filter=d --name-only | grep -E "$REGEX")

echo "$CHANGED_FILES"
