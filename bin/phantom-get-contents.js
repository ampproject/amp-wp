var page = require('webpage').create(),
        system = require('system'),
        address;
var results = [];

if (system.args.length === 1) {
    console.log('Usage: phantom-get-contents.js <some URL>');
    phantom.exit();
}

address = system.args[1];
console.log("Address: " +address);
page.open(address, function(status) {
    console.log("Status: " +status);
    if ( "success" === status ) {
        results['status'] = status;
        results['body'] = page.content;
        console.log("Body: " +results['body']);
        phantom.exit(results);
    } else {
        results['status'] = status;
        phantom.exit(1);
    }

});
