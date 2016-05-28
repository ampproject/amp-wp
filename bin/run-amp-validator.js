#!/usr/bin/env node
/**
 * Gather our URLs to test from the WPX file in our plugin directory and run it through the validator.
 *
 * from the plugin root dir on your server run 'node bin/run-amp-validator.js' to run
 */

// var util = require('util'),
var exec = require('child_process').exec;
function puts(error, stdout, stderr) {
    console.log(stdout);

    // var result = JSON.parse(stdout);
    //
    // for ( var key in result ) {
    //
    //     result = result[key];
    //
    //     if ( ! result.success ) {
    //
    //         console.log(result.errors);
    //
    //     } else {
    //
    //         console.log("Success: "+result.success);
    //
    //     }
    //
    // }
}

exec('amp-validator http://auto-amp.dev/2013/01/01/pingbacks-an-trackbacks/amp/' , puts);

/**
 * This parses our XML to gather the links to our posts so we can test them.
 * TODO: When run on all URLs we are getting resource errors from Node.  Need to investigate and possibly break the validation functions into smaller chunks and prevent concurrence.
 *
 * We also might be able to utilize the BETA Node API listed here: https://github.com/ampproject/amphtml/tree/master/validator
 *
 * TODO: ask the user for their WP base URL
 */

// var amp = require('amp-validator');
//
// var returnJSONResults = function(baseName, queryName) {
//     var XMLPath = "wptest.xml";
//     var rawJSON = loadXMLDoc(XMLPath);
//     function loadXMLDoc(filePath) {
//         var fs = require('fs');
//         var xml2js = require('xml2js');
//         var json;
//         try {
//             var fileData = fs.readFileSync(filePath, 'ascii');
//
//             var parser = new xml2js.Parser();
//             parser.parseString(fileData.substring(0, fileData.length), function (err, result) {
//
//                 var wptestBaseURL = result.rss.channel[0]['wp:base_site_url'],
//                     items = result.rss.channel[0].item;
//                 console.log(items.length);
//
//                 // console.log(channel[0]);
//                 // console.log( wptestBaseURL );
//                 // console.log( items );
//                 var postCount = 0;
//                 var postURLs = '';
//                 for (var i=0 , len = items.length; i< len; i++ ) {
//                     var item = items[i],
//                         localBaseURL = 'http://auto-amp.dev',
//                         postType = item['wp:post_type'][0],
//                         postStatus = item['wp:status'][0];
//
//                     if ( 'post' === postType && 'publish' === postStatus ) {
//                         postCount++;
//                         var postDate = new Date( item['wp:post_date'] ),
//                             link = item.link[0],
//                             postMonth = ("0" + ( postDate.getMonth()+1 ) ).slice(-2),
//                             postDay = ("0" + postDate.getDate()).slice(-2),
//                             localURL = localBaseURL+"/"+postDate.getFullYear()+"/"+postMonth+"/"+postDay;
//
//                         link = link.replace(wptestBaseURL, localURL)+"amp/";
//                         // console.log( link );
//
//                         // var cmd = "amp-validator "+link+" -o json";
//                         var cmd = "amp-validator "+link;
//                         // console.log( cmd );
//                         // amp.validate( link );
//                         postURLs += " "+link;
//                         // exec(cmd , puts);
//                     }
//                 }
//                 console.log(postCount);
//                 // console.log(postURLs);
//             });
//
//             // console.log("File '" + filePath + "/ was successfully read.\n");
//             // return json;
//         } catch (ex) {console.log(ex)}
//     }
// }();