/**
 * External dependencies
 */
import memoize from 'micro-memoize';

// This list is taken directly from: https://reactjs.org/docs/dom-elements.html#all-supported-html-attributes
const DOM_ATTRIBUTES = `accept acceptCharset accessKey action allowFullScreen alt async autoComplete
autoFocus autoPlay capture cellPadding cellSpacing challenge charSet checked
cite classID className colSpan cols content contentEditable contextMenu controls
controlsList coords crossOrigin data dateTime default defer dir disabled
download draggable encType form formAction formEncType formMethod formNoValidate
formTarget frameBorder headers height hidden high href hrefLang htmlFor
httpEquiv icon id inputMode integrity is keyParams keyType kind label lang list
loop low manifest marginHeight marginWidth max maxLength media mediaGroup method
min minLength multiple muted name noValidate nonce open optimum pattern
placeholder poster preload profile radioGroup readOnly rel required reversed
role rowSpan rows sandbox scope scoped scrolling seamless selected shape size
sizes span spellCheck src srcDoc srcLang srcSet start step style summary
tabIndex target title type useMap value width wmode wrap`;

// Allow all the above attributes plus `on*` and `data-*`.
const LEGAL_KEYS = new RegExp( `^(${ DOM_ATTRIBUTES.split( /\s+/g ).join( '|' ) }|on\\w*|data-\\w*)$`, 'gi' );

/**
 * Get valid DOM attributes.
 *
 * This is relevant when passing `...rest` to a node but wanting to filter out
 * all the invalid attributes.
 *
 * @param {Object} attrs  Full object of attributes to filter
 * @return {Object} Filtered object with only valid attributes
 */
function getValidDOMAttributes( attrs ) {
	const keys = Object.keys( attrs );
	const validKeys = keys.filter( ( k ) => LEGAL_KEYS.test( k ) );
	return validKeys.reduce( ( result, key ) => ( { ...result, [ key ]: attrs[ key ] } ), {} );
}

export default memoize( getValidDOMAttributes );
