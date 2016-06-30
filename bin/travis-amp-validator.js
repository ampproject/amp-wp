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
        url             = require('url'),
        chai            = require('chai'),
        assert          = chai.assert;

chai.should();
chai.use(require('chai-things'));

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


describe('AMP Validation Suite', function() {
    this.timeout(1000000);
    var testUrls = [];
    var ourResults = [];

    before( function() {
        return new Promise(function (resolve, reject) {
            exec('wp post list --post_type=post --posts_per_page=-1 --post_status=publish --post_password="" --format=json --fields=url --quiet --skip-plugins=wordpress-importer', function (error, stdout, stderr) {
                if (error) {
                    console.error('exec error: ' + error);
                    process.exit(1);
                }

                var items = JSON.parse(stdout.trim());

                for (var i = 0, len = items.length; i < len; i++) {
                    var item = items[i];

                    if ('/' != item['url'].slice(-1)) {
                        item['url'] = item['url'] + "/";
                    }

                    // testUrls.push(item['url'] + "amp/");

                }

                //Control URLs for Testing purposes
                // var localBaseURL = url.parse(testUrls[0]);
                // localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
                var localBaseURL = 'http://auto-amp.dev';
                testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');
                testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
                // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');

                console.log("Hang tight, we are going to test " + testUrls.length + " urls...");

                const ourInstance = ampValidator.getInstance();
                var i = 0,
                    len = testUrls.length - 1;
                //This runs our list of URLs through the AMP Validator.
                promiseWhile(function() {
                    return i <= len;
                }, function() {
                    return new Promise( function( resolve, reject ) {
                        const horseman = new Horseman();
                        horseman.open(testUrls[i])
                            .status()
                            .then( function(status) {
                                if ( 200 !== Number(status) ) {
                                    var statusMessage = 'Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + status;
                                    // throw statusMessage ;
                                    console.error( statusMessage );
                                    process.exit(1);
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
                                console.log(i)
                                return ourInstance.then(function (validator) {
                                    const result = validator.validateString(body);
                                    console.log(body);
                                    if (result.status === 'PASS') {
                                        console.log(result.status + ": "+testUrls[i]);
                                        ourResults.push('PASS');
                                        resolve();
                                    } else {
                                        let msg = result.status.error + ": " + testUrls[i] + '\n';
                                        console.log(result.errors);
                                        for (const error of result.errors) {
                                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                            if (error.specUrl !== null) {
                                                msg += '\n     (see ' + error.specUrl + ')\n';
                                            }
                                            // ((error.severity === 'ERROR') ? console.error : console.warn)(msg);
                                        }
                                        reject( Error(msg) );
                                    }
                                }).catch(function(e){
                                    i++;
                                    console.log("error: "+e);
                                    ourResults.push(e);
                                    return horseman.close();
                                });
                            })
                            .catch(function(e){
                                i++;
                                console.log("e: "+e);
                                ourResults.push(e);
                                return horseman.close();
                            })
                            .finally( function() {
                                i++;
                                return horseman.close();
                            });
                    });

                });

                var timeout = setInterval(function () {
                    console.log("Our Results:"+ourResults);
                    if (i >= len) {
                        clearInterval(timeout);
                        resolve();
                    }
                }, 500);
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