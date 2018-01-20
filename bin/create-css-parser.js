#!/usr/bin/env node
/* jshint node:true, es3:false, esversion:6 */
/* eslint-env node, es6 */
/* eslint-disable one-var */

const pegjs = require( 'pegjs' );
const phpegjs = require( 'phpegjs' );
const fs = require( 'fs' );
const path = require( 'path' );

const peg = fs.readFileSync( 'bin/css.pegjs', 'utf8' );

const parser = pegjs.generate(
	peg,
	{
		plugins: [ phpegjs ],
		phpegjs: {
			parserNamespace: null,
			parserGlobalNamePrefix: 'AMP_PEG_CSS_',
			mbstringAllowed: true
		}
	}
);

fs.writeFileSync(
	path.join( __dirname, '..', 'includes', 'lib', 'css-parser.php' ),
	parser
);
