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
    this.timeout(100000);
    var testUrls = [];
    var ourResults = [];
    var ourErrors = [];

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
                // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
                // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');
                // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');

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
                                    var response = i+': Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + res.status + ' - ' + res.statusText;
                                    ourErrors.push( response );
                                    ourResults.push( 'FAIL' );
                                    i++;
                                    resolve();
                                }
                            }).then(function(body) {
                            if ( body ) {
                                return ourInstance.then(function (validator) {
                                    const result = validator.validateString(body);
                                    if (result.status === 'PASS') {
                                        ourResults.push('PASS');
                                    } else {
                                        let msg = result.status.error + ": " + testUrls[i] + '\n';
                                        for (const error of result.errors) {
                                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                            if (error.specUrl !== null) {
                                                msg += '\n     (see ' + error.specUrl + ')';
                                            }
                                        }
                                        ourErrors.push(i+": "+msg);
                                        ourResults.push('FAIL');
                                    }
                                    i++;
                                    resolve();
                                });
                            }
                            i++;
                            resolve();
                        })
                    });

                });
                var timeout = setInterval(function(){
                    if (i >= len) {
                        clearInterval(timeout);
                        if (ourErrors.length > 0) {
                            console.log('----------------------------------------------------------------------------'.error);
                            console.log('---------------------------------Errors-------------------------------------'.error);
                            console.log('----------------------------------------------------------------------------\n'.error);
                            for (var j = 0, num = ourErrors.length; j < num; j++) {
                                console.log('||||||||||||||||||||||||||||||        ' + (j + 1) + '        ||||||||||||||||||||||||||||||');
                                console.log(ourErrors[j]);
                                console.log('|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||\n');
                            }
                            console.log('----------------------------------------------------------------------------'.error);
                            console.log('----------------------------------------------------------------------------\n'.error);
                        }
                        resolve();
                    }
                },500);

            });
        });
    });

    it('Get URLs from WP', function(){
        testUrls.length.should.not.equal(0);
    });
    it('Get Validation Results', function(){
        ourResults.length.should.not.equal(0);
    });
    it('All URLs correctly validate', function(){
        ourResults.should.all.be.equal('PASS');
    });
    it('No Errors found', function(){
        ourErrors.length.should.equal(0);
    });


});