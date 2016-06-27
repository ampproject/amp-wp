#!/usr/bin/env node
/**
 * Gather our URLs to test from the WPX file in our plugin directory and run it through the validator.
 *
 * from the plugin root dir on your server run 'node bin/run-amp-validator.js' to run
 */

'use strict';

const Promise = require('bluebird'),
    ampValidator = require('amp-html/validator'),
    fetch = require('node-fetch'),
    exec = require('child_process').exec,
    colors = require('colors'),
    url = require('url');

colors.setTheme({
    info: 'green',
    debug: 'blue',
    error: 'red'
});

var promiseWhile = function(condition, action) {
    var resolver = Promise.defer();

    var loop = function() {
        if (!condition()) return resolver.resolve();
        return Promise.cast(action())
            .then(loop)
            .catch(resolver.reject);
    };

    process.nextTick(loop);

    return resolver.promise;
};


//run a WP-CLI command to collect our installs post URLS and test them
exec('wp post list --post_type=post --posts_per_page=-1 --post_status=publish --post_password="" --format=json --fields=url --quiet --skip-plugins=wordpress-importer', function(error, stdout, stderr) {
    if (error) {
        console.error('exec error: '+error);
        process.exit(1);
    }

    var testUrls = [];
    var items = JSON.parse(stdout.trim());

    for (var i=0 , len = items.length; i < len; i++ ) {
        var item = items[i];

        testUrls.push( item['url']+"amp/" );

    }

    //Control URLs for Testing purposes
    var localBaseURL = url.parse(testUrls[0]);
    localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
    testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/bin/failure.html' );
    testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/bin/success.html' );

    var i = 0,
        len = testUrls.length - 1;
    console.log("Hang tight, we are going to test "+testUrls.length+" urls...");

    const ourInstance = ampValidator.getInstance();
    //This runs our list of URLs through the AMP Validator.
    promiseWhile(function() {
        return i <= len;
    }, function() {
        return new Promise( function( resolve, reject ) {
            fetch( testUrls[i] )
                .then( function(res) {
                    console.log('Inside fetch');
                    console.log(res);
                    return res;
                })
                .then( function( res ) {
                    console.log('Inside fetch then');
                    if ( res.ok ) {
                        return res.text();
                    } else {
                        var response = (i + 1) + ': Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + res.status + ' - ' + res.statusText;
                        console.error(response.error);
                    }
                }).then(function(body) {
                console.log('Inside fetch then then');
                if ( body ) {
                    console.log('Inside if body');
                    return ourInstance.then(function (validator) {
                        const result = validator.validateString(body);
                        if (result.status === 'PASS') {
                            console.log( result.status.info + ": " + testUrls[i] );
                        } else {
                            console.error( result.status.error + ": " + testUrls[i]);
                        }

                        for (const error of result.errors) {
                            let msg = ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                            if (error.specUrl !== null) {
                                msg += '\n     (see ' + error.specUrl + ')';
                            }
                            ((error.severity === 'ERROR') ? console.error : console.warn)(msg);
                        }
                        i++;
                        resolve();
                    });
                } else {
                    i++;
                    resolve();
                }

            });

        }).catch( function(e){
            console.error(e);
            process.exit(1);
        });

    });

});
phantom.exit();