var page = require('webpage').create(),
        system = require('system'),
        address;
var results = [];

if (system.args.length === 1) {
    console.log('Usage: phantom-get-contents.js <some URL>');
    phantom.exit();
}

address = system.args[1];
console.log(address);
page.open(address, function(status) {
    results['status'] = status;
    console.log(status);
    results['body'] = page.evaluate(function() {
        return document;
    })
    return results;
    phantom.exit();
});
