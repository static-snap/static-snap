#!/usr/bin/env bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SCRIPT_PARENT_DIR="$(dirname "$SCRIPT_DIR")"

TMP_DIR="$SCRIPT_PARENT_DIR/tmp"

REPOSITORY_URL="git://develop.git.wordpress.org/"
CLONE_DIR="$TMP_DIR/wordpress-tests-lib"

# if directory $WP_UNIT_DIR exists, remove it
if [ -d $CLONE_DIR ]; then
    rm -rf $CLONE_DIR
fi


git clone  --depth 1  $REPOSITORY_URL $CLONE_DIR

#copy the wp-tests-config-sample.php to wp-tests-config.php
cp $CLONE_DIR/wp-tests-config-sample.php $CLONE_DIR/wp-tests-config.php

#replace the database/hostname name to wordpress
sed -i '' 's/youremptytestdbnamehere/wordpress/g' $CLONE_DIR/wp-tests-config.php
sed -i '' 's/yourusernamehere/wordpress/g' $CLONE_DIR/wp-tests-config.php
sed -i '' 's/yourpasswordhere/wordpress/g' $CLONE_DIR/wp-tests-config.php
sed -i '' 's/localhost/db/g' $CLONE_DIR/wp-tests-config.php






