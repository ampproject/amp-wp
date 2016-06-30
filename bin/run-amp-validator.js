/**
 * Tool to locally gather our URLs to test and run them through the validator.
 *
 * from the plugin root dir on your server run 'mocha bin/run-amp-validator.js' to run
 */

'use strict'

const Promise       = require('bluebird'),
    ampValidator    = require('amp-html/validator'),
    fetch           = require('node-fetch'),
    exec            = require('child_process').exec,
    colors          = require('colors'),
    url             = require('url'),
    chai            = require('chai'),
    assert          = chai.assert;

chai.should();
chai.use(require('chai-things'));


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

describe('AMP Validation Suite', function() {
    this.timeout(10000);
    var testUrls = [];
    var ourResults = [];

    before( function() {
        return new Promise( function(resolve, reject){
            exec('wp post list --post_type=post --posts_per_page=-1 --post_status=publish --post_password="" --format=json --fields=url --quiet --skip-plugins=wordpress-importer', function(error, stdout, stderr) {
                if (error || stderr) {
                    reject(error);
                }

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
                // testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/404.html' );
                // testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/failure.html' );
                testUrls.push( localBaseURL+'/wp-content/plugins/amp-wp/tests/assets/success.html' );

                console.log("Hang tight, we are going to test "+testUrls.length+" urls...");

                const ourInstance = ampValidator.getInstance();
                var i = 0,
                    len = testUrls.length - 1;
                //This runs our list of URLs through the AMP Validator.
                promiseWhile(function() {
                    return i <= len;
                }, function() {
                    return new Promise( function( resolve, reject ) {
                        fetch( testUrls[i] )
                            .then( function( res ) {
                                if ( res.ok ) {
                                    return res.text();
                                } else {
                                    var response = 'Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + res.status + ' - ' + res.statusText;
                                    console.error(response);
                                    ourResults.push('Unable to Fetch'+ testUrls[i]);
                                    resolve();
                                }
                            }).then(function(body) {
                            if ( body ) {
                                return ourInstance.then(function (validator) {
                                    const result = validator.validateString(body);
                                    if (result.status === 'PASS') {
                                        ourResults.push('PASS');
                                        i++;
                                    } else {
                                        let msg = result.status.error + ": " + testUrls[i] + '\n';
                                        for (const error of result.errors) {
                                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                            if (error.specUrl !== null) {
                                                msg += '\n     (see ' + error.specUrl + ')';
                                            }
                                        }
                                        console.error(testUrls[i]+"returned:\n"+msg);
                                        ourResults.push('FAIL');
                                        i++;
                                    }
                                    resolve();
                                });
                            } else {
                                i++;
                                console.error('Body of '+ testUrls[i] + ' was empty'.error);
                                ourResults.push('FAIL');
                            }
                            resolve();
                        })
                    });

                });
                var timeout = setInterval(function(){
                    if (i >= len) {
                        clearInterval(timeout);
                        resolve();
                    }
                },500);

            });
        });
    });

    it('Get URLs from WP', function(){
        testUrls.length.should.not.equal(0);
    });
    it('All URLs correctly validate', function(){
        ourResults.should.all.be.equal('PASS');
    });

});