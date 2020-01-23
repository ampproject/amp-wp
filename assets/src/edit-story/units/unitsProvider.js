/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import StoryPropTypes from '../types';
import Context from './context';
import {
	dataToEditorX,
	dataToEditorY,
	editorToDataX,
	editorToDataY,
	getBox,
} from './dimensions';

function UnitsProvider( { pageSize, children } ) {
	const { width: pageWidth, height: pageHeight } = pageSize;
	const state = useMemo(
		() => ( {
			state: {
				pageSize: { width: pageWidth, height: pageHeight },
			},
			actions: {
				dataToEditorX: ( x ) => dataToEditorX( x, pageWidth ),
				dataToEditorY: ( y ) => dataToEditorY( y, pageHeight ),
				editorToDataX: ( x ) => editorToDataX( x, pageWidth ),
				editorToDataY: ( y ) => editorToDataY( y, pageHeight ),
				getBox: ( element ) => getBox( element, pageWidth, pageHeight ),
			},
		} ),
		[ pageWidth, pageHeight ] );

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

UnitsProvider.propTypes = {
	pageSize: StoryPropTypes.size.isRequired,
	children: StoryPropTypes.children.isRequired,
};

export default UnitsProvider;
