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
    results['status'] = status;
    console.log("Status: " +status);
    results['body'] = page.evaluate(function() {
        return document.body.text();
    })
    console.log("Body: " +results['body']);
    // return results;
    phantom.exit();
});
