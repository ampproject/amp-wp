/**
 * External dependencies
 */
import {
	Children,
	cloneElement,
	Component,
	createContext,
	createElement,
	createRef,
	forwardRef,
	Fragment,
	isValidElement,
	memo,
	StrictMode,
	useState,
	useEffect,
	useContext,
	useReducer,
	useCallback,
	useMemo,
	useRef,
	useImperativeHandle,
	useLayoutEffect,
	useDebugValue,
	lazy,
	Suspense,
} from 'react';
import { isString } from 'lodash';

export {
	createPortal,
	findDOMNode,
	render,
	unmountComponentAtNode,
} from 'react-dom';

// Just pass these through from React.
export {
	Children,
	cloneElement,
	Component,
	createContext,
	createElement,
	createRef,
	forwardRef,
	Fragment,
	isValidElement,
	memo,
	StrictMode,
	useState,
	useEffect,
	useContext,
	useReducer,
	useCallback,
	useMemo,
	useRef,
	useImperativeHandle,
	useLayoutEffect,
	useDebugValue,
	lazy,
	Suspense,
};

// These 3 functions below have to be defined here to use the correct React functions imported above.
// The first 2 are copied straight from @wordpress/element:src/react.js
export function concatChildren( ...childrenArguments ) {
	return childrenArguments.reduce( ( result, children, i ) => {
		Children.forEach( children, ( child, j ) => {
			if ( child && 'string' !== typeof child ) {
				child = cloneElement( child, {
					key: [ i, j ].join(),
				} );
			}

			result.push( child );
		} );

		return result;
	}, [] );
}

export function switchChildrenNodeName( children, nodeName ) {
	return children && Children.map( children, ( elt, index ) => {
		if ( isString( elt ) ) {
			return createElement( nodeName, { key: index }, elt );
		}
		const { children: childrenProp, ...props } = elt.props;
		return createElement( nodeName, { key: index, ...props }, childrenProp );
	} );
}

// This function is copied straight from @wordpress/element:src/raw-html.js
export const RawHTML = ( { children, ...props } ) => {
	// The DIV wrapper will be stripped by serializer, unless there are
	// non-children props present.
	return createElement( 'div', {
		dangerouslySetInnerHTML: { __html: children },
		...props,
	} );
};

// This can be re-exported after being imported from the actual package.
const { isEmptyElement } = require.requireActual( '@wordpress/element' );
export {
	isEmptyElement,
};

/* This mock module is explicitly *not* copying `renderToString`.
 * It's possible to simple copy the entire complex function, but most likely not needed at
 * all for any tests this repo will be doing.
 */
