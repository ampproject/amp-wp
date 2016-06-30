#!/usr/bin/env node
/**
 * Gather our URLs to test from the WPX file in our plugin directory and run it through the validator.
 *
 * from the plugin root dir on your server run 'node bin/run-amp-validator.js' to run
 */

'use strict';

const   Promise         = require('bluebird'),
        ampValidator    = require('amp-html/validator'),
        Horseman        = require('node-horseman'),
        childProcess    = require('child_process'),
        exec            = childProcess.exec,
        colors          = require('colors'),
        url             = require('url');

colors.setTheme({
    info:   'green',
    debug:  'blue',
    error:  'red'
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

        if ( '/' != item['url'].slice(-1) ) {
            item['url'] = item['url']+"/";
        }

        testUrls.push( item['url']+"amp/" );

    }

    //Control URLs for Testing purposes
    var localBaseURL = url.parse(testUrls[0]);
    localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
    testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/404.html' );
    testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/failure.html' );
    testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/success.html' );
    
    console.log("Hang tight, we are going to test "+testUrls.length+" urls...");
    validateUrls(testUrls);

});

var validateUrls = function(testUrls){
    const ourInstance = ampValidator.getInstance();
    var i = 0,
        len = testUrls.length - 1;
    //This runs our list of URLs through the AMP Validator.
    promiseWhile(function() {
        return i <= len;
    }, function() {
        return new Promise( function( resolve, reject ) {
            var body = '';
            var hasErrors = false;
            var foundErrors = [];
            const horseman = new Horseman();
            horseman.open(testUrls[i])
                .status()
                .then( function(status) {
                    if ( 200 !== Number(status) ) {
                        var statusMessage = 'Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + status;
                        reject( Error( statusMessage ) );
                        // console.error( statusMessage );
                    }
                    // resolve();
                })
                .evaluate( function() {
                        var getDocTypeAsString = function () {
                            var node = document.doctype;
                            return node ? "<!DOCTYPE "
                            + node.name
                            + (node.publicId ? ' PUBLIC "' + node.publicId + '"' : '')
                            + (!node.publicId && node.systemId ? ' SYSTEM' : '')
                            + (node.systemId ? ' "' + node.systemId + '"' : '')
                            + '>\n' : '';
                        };
                        var htmlDoc = document.documentElement.outerHTML.replace(/&lt;/g, '<')
                        htmlDoc = htmlDoc.replace(/&gt;/g, '>');
                        return getDocTypeAsString() + htmlDoc;

                })
                .then( function(body) {
                    if (!body) {
                        body = '';
                    }
                    return ourInstance.then(function (validator) {
                        const result = validator.validateString(body);
                        if (result.status === 'PASS') {
                            console.log( result.status.info + ": " + testUrls[i] );
                            resolve();
                        } else {
                            let msg = result.status.error + ": " + testUrls[i] + '\n';

                            for (const error of result.errors) {
                                msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                if (error.specUrl !== null) {
                                    msg += '\n     (see ' + error.specUrl + ')';
                                }
                                // ((error.severity === 'ERROR') ? console.error : console.warn)(msg);
                            }
                            reject( Error(msg) );
                        }
                    });
                })
                .catch(function(e){
                    i++;
                    console.error(e);
                    return horseman.close();
                })
                .finally( function() {
                    i++;
                    return horseman.close();
                });
        });

    });
}