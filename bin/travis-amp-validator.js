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
    var ourErrors = [];

    before( function() {
        return new Promise(function (resolve, reject) {
            exec('wp post list --post_type=post --posts_per_page=-1 --post_status=publish --post_password="" --format=json --fields=url --quiet --skip-plugins=wordpress-importer', function (error, stdout, stderr) {
                if (error) {
                    console.error('exec error: ' + error);
                    process.exit(1);
                }

                var items = JSON.parse(stdout.trim());

                for (var h = 0, itemLength = items.length; h < itemLength ; h++) {
                    var item = items[h];

                    if ('/' != item['url'].slice(-1)) {
                        item['url'] = item['url'] + "/";
                    }

                    // testUrls.push(item['url'] + "amp/");

                }

                //Control URLs for Testing purposes
                // var localBaseURL = url.parse(testUrls[0]);
                // localBaseURL = localBaseURL.protocol + "//" + localBaseURL.hostname;
                var localBaseURL = 'http://auto-amp.dev';
                testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/success.html');
                testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/failure.html');
                testUrls.push(localBaseURL + '/wp-content/plugins/amp-wp/tests/assets/404.html');

                console.log("Hang tight, we are going to test " + testUrls.length + " urls...");

                const ourInstance = ampValidator.getInstance();
                var i = 0,
                    len = testUrls.length - 1;
                console.log("len:"+len);
                //This runs our list of URLs through the AMP Validator.
                promiseWhile(function() {
                    return i <= len;
                }, function() {
                    return new Promise( function( resolve, reject ) {
                        const horseman = new Horseman();
                        var url = testUrls[i];
                        console.log(url);
                        horseman.open(url)
                            .status()
                            .then( function(status) {
                                console.log("Status: " + status);
                                console.log(i+":       " + url);
                                if ( 200 !== Number(status) ) {
                                    var statusMessage = 'FAIL: Unable to fetch ' + url + ' - HTTP Status ' + status+"\n";
                                    console.log( i + ": " + status + ": " + url );
                                    ourErrors.push( statusMessage );
                                    ourResults.push( statusMessage );
                                    return Promise.reject();
                                }
                                resolve();
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
                                return ourInstance.then(function (validator) {
                                    var result = '';
                                    result = validator.validateString(body);
                                    if (result.status === 'PASS') {
                                        console.log(i+": "+result.status.info + ": "+url);
                                        ourResults.push('PASS');
                                    } else {
                                        let msg = i+": "+result.status.error + ": " + url + '\n';
                                        for (const error of result.errors) {
                                            msg += ('     line ' + error.line + ', col ' + error.col + ': ').debug + error.message.error;
                                            if (error.specUrl !== '') {
                                                msg += '\n     (see ' + error.specUrl + ')\n';
                                            }
                                            // ((error.severity === 'ERROR') ? console.error : console.warn)(msg);
                                        }
                                        console.log(i+": FAIL: ".error + url);
                                        ourErrors.push(msg);
                                        ourResults.push(msg);
                                    }

                                });
                            }).then( function(){
                                i++;
                            })
                            .catch(function(e){
                                ourErrors.push(e);
                                ourResults.push(e);
                            })
                            .finally( function() {
                                return horseman.close();
                            });

                    });

                });

                var timeout = setInterval(function () {
                    console.log(i);
                    if (i > len) {
                        clearInterval(timeout);
                        if (ourErrors.length > 0) {
                            console.log('----------------------------------------------------------------------------'.error);
                            console.log('---------------------------------Errors-------------------------------------'.error);
                            console.log('----------------------------------------------------------------------------\n'.error);
                            for (var j = 0, num = ourErrors.length-1; j < num; j++) {
                                console.log('||||||||||||||||||||||||||||||        ' + (j + 1) + '        ||||||||||||||||||||||||||||||');
                                console.log(ourErrors[j]);
                                console.log('|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||\n');
                            }
                            console.log('----------------------------------------------------------------------------'.error);
                            console.log('----------------------------------------------------------------------------\n'.error);
                        }
                        resolve();
                    }
                }, 5000);
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