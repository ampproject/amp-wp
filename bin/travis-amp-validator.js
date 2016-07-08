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
 * @param ms
 * @param promise
 */
var timeout = function (ms, promise) {
    return new Promise(function(resolve, reject){
        promise.then(resolve);
        setTimeout(function(){
            reject( new Error('Timeout after '+ms+'ms'));
        }, ms);
    });
};


describe('AMP Validation Suite', function() {
    this.timeout(1000000);
    var testUrls = [];
    var ourResults = [];
    var ourErrors = [];

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

            //Control URLs for Testing purposes
            var localBaseURL = url.parse(testUrls[0]);
            localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
            // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
            testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');
            // testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');


        });
        child.stderr.on('data', function (data) {
            ourErrors.push('stderr: ' + data);
        });
        return promiseFromChildProcess(child).then(function () {
            console.log("Hang tight, we are going to test "+testUrls.length+" urls...");
        }, function (err) {
            ourErrors.push('stderr: ' + err);
        }).then(function() {
            const ourInstance = ampValidator.getInstance();
            var i = 0,
                len = testUrls.length;

            return promiseWhile(function() {
                return i <= len;
            }, function() {
                return new Promise( function( resolve, reject ) {
                    const horseman = new Horseman();
                    var url = testUrls[i];
                    if (url) {
                        horseman.open(url)
                            .status()
                            .then(function (status) {
                                if (200 !== Number(status)) {
                                    var statusMessage = "FAIL: ".error + ' Unable to fetch ' + url + ' - HTTP Status ' + status + "\n";
                                    ourErrors.push(statusMessage);
                                    ourResults.push(statusMessage);
                                    resolve(statusMessage);
                                } else {
                                    resolve();
                                }
                            })
                            .evaluate(function () {
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
                            .then(function (body) {
                                return ourInstance.then(function (validator) {
                                    var result = validator.validateString(body);
                                    if (result.status === 'PASS') {
                                        console.log(i + ": " + result.status.info + ": " + url);
                                        ourResults.push('PASS');
                                    } else {
                                        let msg = i + ": " + result.status.error + ": " + url + '\n';
                                        for (const error of result.errors) {
                                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                            if (error.specUrl !== '') {
                                                msg += '\n     (see ' + error.specUrl + ')\n';
                                            }
                                            // ((error.severity === 'ERROR') ? console.error : console.warn)(msg);
                                        }
                                        console.log(i + ": FAIL: ".error + url);
                                        ourErrors.push(msg);
                                        ourResults.push(msg);
                                    }
                                    i++;
                                    resolve();
                                });
                            }).close();
                    } else {
                        i++;
                        resolve();
                    }
                })
                .catch(function(e){
                    ourErrors.push(e);
                    ourResults.push(e);
                });
            });
        }).then(function(){
            setTimeout(function(){
                resolve();
            }, 500);
        }).then(function(){
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