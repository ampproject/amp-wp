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

var testUrls = [];

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

var validateUrl = function(url){
    return new Promise( function( resolve, reject ) {
        fetch(url)
        .then(function (res) {

            if ( res.ok ) {
                return res.text();
            } else {
                var response = new Error ('Unable to fetch ' + res.url + ' - HTTP Status ' + res.status + ' - ' + res.statusText);
                reject( response );
            }

        }).then(function (body) {
            const ourInstance = ampValidator.getInstance();
            if (body) {

                ourInstance.then(function (validator) {

                    const result = validator.validateString(body);
                    if (result.status === 'PASS') {
                        resolve( result.status );
                    } else {
                        let msg = url + '\n';
                        for (const error of result.errors) {
                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                            if (error.specUrl !== null) {
                                msg += '\n     (see ' + error.specUrl + ')';
                            }
                        }
                        var errorMessage = new Error ( msg );
                        reject( errorMessage );
                    }
                });
            } else {
                var error = new Error('FAIL - no body');
                reject( error );
            }

        }).catch(function(error){
            return done(error);
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
            
            var localBaseURL = url.parse(testUrls[0]);
            localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
            testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
            testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');
            testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');

            if ( 0 === testUrls.length ) {
                console.error('URLs not retrieved.'.error);
                process.exit(1);
            }

        });
        child.stderr.on('data', function (data) {
            console.error('Child Exec Errored: '.error + data.error);
            process.exit(1);
        });

        return promiseFromChildProcess(child)
        .then(function () {
                console.log("Hang tight, we are going to test "+testUrls.length+" urls...");
            }, function (err) {
                console.error('Child Exec rejected: ' + err);
                process.exit(1);
        })
        .then(function() {
            describe('Dynamic tests for URLs', function(){
                testUrls.forEach( function( url ) {
                    if ( '404.html' == url.substr(url.length - 8) || 'failure.html' == url.substr(url.length - 12)) {
                        it( url + ' should NOT validate', function(done) {
                            validateUrl( url ).
                            then(function(urlResult){
                                urlResult.should.equal('PASS');
                                done(urlResult);
                            }).catch(function(error){
                                done();
                            });
                        });
                    } else {
                        it( url + ' should validate', function(done) {
                            validateUrl( url ).
                            then(function(urlResult){
                                urlResult.should.equal('PASS');
                                done();
                            }).catch(function(error){
                                done(error);
                            });
                        });
                    }
                });
            });
        });
    });


    it('Control Test to Make Mocha run before()', function () {
        // console.log('Mocha should not require this hack IMHO');
    });





});