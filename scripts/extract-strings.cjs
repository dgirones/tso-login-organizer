/**
 * Extract gettext strings into scripts/_strings.txt.
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const root = path.join( __dirname, '..' );

/**
 * Walk PHP files.
 *
 * @param {string} dir Directory.
 * @param {string[]} acc Accumulator.
 * @return {string[]}
 */
function walk( dir, acc = [] ) {
	for ( const entry of fs.readdirSync( dir, { withFileTypes: true } ) ) {
		const full = path.join( dir, entry.name );
		if ( entry.isDirectory() ) {
			if ( 'scripts' !== entry.name && 'vendor' !== entry.name && 'languages' !== entry.name ) {
				walk( full, acc );
			}
		} else if ( full.endsWith( '.php' ) ) {
			acc.push( full );
		}
	}
	return acc;
}

const files = walk( root );
const re =
	/(?:__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e)\(\s*'((?:\\'|[^'])*)'\s*,\s*'tso-login-organizer'\s*\)/g;
const set = new Set( [
	'Frontend login, registration and account pages with optional premium modules. Drop-in replacement for Theme My Login.',
] );

for ( const file of files ) {
	const text = fs.readFileSync( file, 'utf8' );
	let match;
	while ( ( match = re.exec( text ) ) ) {
		set.add( match[ 1 ].replace( /\\'/g, "'" ) );
	}
}

const out = [ ...set ].sort().join( '\n' ) + '\n';
fs.writeFileSync( path.join( __dirname, '_strings.txt' ), out );
process.stdout.write( `Wrote ${ set.size } strings\n` );
