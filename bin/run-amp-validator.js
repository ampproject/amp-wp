/**
 * Tool to locally gather our URLs to test and run them through the validator.
 *
 * from the plugin root dir on your server run 'mocha bin/run-amp-validator.js' to run
 */

'use strict';

const Promise       = require('bluebird'),
    ampValidator    = require('amp-html/validator'),
    fetch           = require('node-fetch'),
    exec            = require('child_process').exec,
    colors          = require('colors'),
    url             = require('url'),
    chai            = require('chai');

var testUrls = [],
    ourResults = [],
    ourErrors = [];

chai.should();
chai.use(require('chai-things'));

colors.setTheme({
    info: 'green',
    debug: 'blue',
    error: 'red'
});


/**
 * Promisifying exec children
 * adapted from: http://stackoverflow.com/a/30883005
 */
var promiseFromChildProcess = function(child) {
    return new Promise(function (resolve, reject) {
        child.addListener("error", reject);
        child.addListener("exit", resolve);
    });
};


/**
 * While Looping for Promises
 * adapted from: http://blog.victorquinn.com/javascript-promise-while-loop
 */
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

/**
 * Timeout promise
 * adapted from: http://exploringjs.com/es6/ch_promises.html
 * @param ms integer time in milliseconds before timing out our promise with a reject()
 * @param promise string name of promise function to timeout
 */
var timeout = function (ms, promise) {
    return new Promise(function(resolve, reject){
       promise.then(resolve);
        setTimeout(function(){
            reject( new Error('Timeout after '+ms+'ms'));
        }, ms);
    });
};

/**
 * Output the errors stored in ourErrors
 * @param   errors  array of error messages
 */
var outputErrors = function( errors ){
    console.log('----------------------------------------------------------------------------'.error);
    console.log('---------------------------------Errors-------------------------------------'.error);
    console.log('----------------------------------------------------------------------------\n'.error);
    for (var i = 0, num = errors.length; i < num; i++) {
        console.log('||||||||||||||||||||||||||||||        ' + (i + 1) + '        ||||||||||||||||||||||||||||||');
        console.log(errors[i]);
        console.log('|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||\n');
    }
    console.log('----------------------------------------------------------------------------'.error);
    console.log('----------------------------------------------------------------------------\n'.error);
};

/**
 * Validate urls and add results to our* results variables
 * @param   errors  array of error messages
 */
var validateUrls = function( urls ) {
    const ourInstance = ampValidator.getInstance();
    var i = 0,
        len = urls.length - 1;

    return promiseWhile(function() {
        return i <= len;
    }, function() {
        return new Promise( function( resolve, reject ) {
            var url = urls[i];
            fetch( url )
                .then( function( res ) {
                    if ( res.ok ) {
                        return res.text();
                    } else {
                        var response = 'FAIL: '.error + 'Unable to fetch ' + url + ' - HTTP Status ' + res.status + ' - ' + res.statusText + '\n';
                        ourErrors.push( response );
                        ourResults.push( 'FAIL' );
                        i++;
                        resolve();
                    }
                }).then(function(body) {
                    if (body) {
                        //We use timeout here because the AMP validator tool needs an internet connection.  If you are testing locally we want this to fail.  Without the timeout mocha was just failing assertion 1, but didn't provide any information as to why it failed.
                        return timeout(2000,
                            ourInstance)
                            .then(function (validator) {
                                const result = validator.validateString(body);
                                if (result.status === 'PASS') {

                                    ourResults.push('PASS');

                                } else {
                                    let msg = result.status.error + ": " + url + '\n';
                                    for (const error of result.errors) {
                                        msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                        if (error.specUrl !== null) {
                                            msg += '\n     (see ' + error.specUrl + ')';
                                        }
                                    }
                                    ourErrors.push(msg);
                                    ourResults.push('FAIL');
                                }
                                i++;
                                resolve();
                            }).catch(function (reason) {
                                i++;
                                ourErrors.push(reason);
                                console.error('Error or timeout', reason);
                                reject(reason);
                            });
                    }
                });
        });
    });
};

describe('AMP Validation Suite', function() {
    this.timeout(20000);

    before( function() {
        var child = exec('wp post list --post_type=post --posts_per_page=-1 --post_status=publish --post_password="" --format=json --fields=url --quiet --skip-plugins=wordpress-importer');

        child.stdout.on('data', function (data) {
            var items = JSON.parse(data.trim());

            for (var i=0 , len = items.length; i < len; i++ ) {
                var item = items[i];
                if ( '/' != item['url'].slice(-1) ) {
                    item['url'] = item['url']+"/";
                }
                testUrls.push( item['url']+"amp/" );

            }

            /**
             * Control URLs for Testing purposes
             * comment out for production usage
             */
            // var localBaseURL = url.parse(testUrls[0]);
            // localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
            // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
            // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');
            // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');

        });
        child.stderr.on('data', function (data) {

        });

        return promiseFromChildProcess(child)
        .then(function () {
                console.log("Hang tight, we are going to test "+testUrls.length+" urls...");
            }, function (err) {
                console.log('Child Exec rejected: ' + err);
        }).then(function() {

            return validateUrls( testUrls );

        }).then(function(){

            if (ourErrors.length > 0) {

                //call our output errors function to print the errors in the console.
                outputErrors( ourErrors );

            }

        });
    });

    it('Get URLs from WP', function(){
        testUrls.length.should.not.equal(0);
    });

    it('Get All Validation Results', function(){
        ourResults.length.should.equal(testUrls.length);
    });

    it('All URLs correctly validate', function(){
        ourResults.length.should.equal(testUrls.length);
        ourResults.should.all.be.equal('PASS');
    });
    it('No Errors found', function(){
        ourResults.length.should.equal(testUrls.length);
        ourErrors.length.should.equal(0);
    });



});