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
    colors = require('colors');

colors.setTheme({
    info: 'green',
    debug: 'blue',
    error: 'red'
});

/**
 * This parses our XML to gather the links to our posts so we can test them.
 *
 */

function loadXMLDoc(filePath, localBaseURL) {
    var fs = require('fs');
    var xml2js = require('xml2js');
    var urls = [];
    try {
        var fileData = fs.readFileSync(filePath, 'ascii');

        var parser = new xml2js.Parser();
        parser.parseString(fileData.substring(0, fileData.length), function (err, result) {

            var wptestBaseURL = result.rss.channel[0]['wp:base_site_url'],
                items = result.rss.channel[0].item;

            var postCount = 0;

            for (var i=0 , len = items.length; i< len; i++ ) {
                var item = items[i],
                    postType = item['wp:post_type'][0],
                    postStatus = item['wp:status'][0],
                    postPassword = item['wp:post_password'][0];

                if ( 'post' === postType && 'publish' === postStatus && '' === postPassword ) {
                    postCount++;
                    var postDate = new Date( item['wp:post_date'] ),
                        link = item.link[0],
                        postMonth = ("0" + ( postDate.getMonth()+1 ) ).slice(-2),
                        postDay = ("0" + postDate.getDate()).slice(-2),
                        localURL = localBaseURL+"/"+postDate.getFullYear()+"/"+postMonth+"/"+postDay;

                    link = link.replace(wptestBaseURL, localURL)+"amp/";

                    urls.push( link );

                }
            }

        });
        //Control URLs for Testing purposes
        urls.push( localBaseURL+'/wp-content/plugins/amp-wp/bin/failure.html' );
        urls.push( localBaseURL+'/wp-content/plugins/amp-wp/bin/success.html' );
        return urls;
    } catch (ex) {console.log(ex)}
}

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


//At this point, our user will be prompted for their local install URL
exec('wp option get siteurl --quiet --skip-plugins=wordpress-importer', (error, stdout, stderr) => {
    if (error) {
        console.error(`exec error: ${error}`);
        return;
    }
    var localUrl = stdout.replace(/(\r\n|\n|\r)/gm,"");

    //Get our XML path and parse the document to gather our post URLs
    var XMLPath = "wptest.xml",
        testUrls = loadXMLDoc(XMLPath, localUrl);

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
                .then( function( res ) {
                    if ( res.ok ) {
                        return res.text();
                    } else {
                        var response = (i + 1) + ': Unable to fetch ' + testUrls[i] + ' - HTTP Status ' + res.status + ' - ' + res.statusText;
                        console.log(response.error);
                    }
                }).then(function(body) {
                    if ( body ) {
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

        });

    });

});
