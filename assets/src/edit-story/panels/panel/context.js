/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

const panelContext = createContext( { state: {}, actions: {} } );

export default panelContext;
