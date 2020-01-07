/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { useCallback, renderToString } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useClipboardHandlers from '../../utils/useClipboardHandlers';
import { getDefinitionForType } from '../../elements';

const DOUBLE_DASH_ESCAPE = '_DOUBLEDASH_';

/**
 * @param {?Element} container
 */
function useCanvasSelectionCopyPaste( container ) {
	const {
		state: { currentPage, selectedElements },
		actions: { appendElementToCurrentPage, deleteSelectedElements },
	} = useStory();

	const copyCutHandler = useCallback(
		( evt ) => {
			const { type: eventType, clipboardData } = evt;

			if ( selectedElements.length === 0 ) {
				return;
			}

			const payload = {
				sentinel: 'story-elements',
				// @todo: Ensure that there's no unserializable data here. The easiest
				// would be to keep all serializable data together and all non-serializable
				// in a separate property.
				items: selectedElements.map( ( element ) => ( {
					...element,
					basedOn: element.id,
					id: undefined,
				} ) ),
			};
			const serializedPayload = JSON.stringify( payload ).replace( /\-\-/g, DOUBLE_DASH_ESCAPE );

			const textContent = selectedElements
				.map( ( { type, ...rest } ) => {
					const { TextContent } = getDefinitionForType( type );
					if ( TextContent ) {
						return TextContent( { ...rest } );
					}
					return type;
				} )
				.join( '\n' );

			const htmlContent = selectedElements
				.map( ( { type, ...rest } ) => {
					// eslint-disable-next-line @wordpress/no-unused-vars-before-return
					const { Save } = getDefinitionForType( type );
					return renderToString( <Save { ...rest } /> );
				} )
				.join( '\n' );

			clipboardData.setData( 'text/plain', textContent );
			clipboardData.setData( 'text/html', `<!-- ${ serializedPayload } -->${ htmlContent }` );

			if ( eventType === 'cut' ) {
				deleteSelectedElements();
			}

			evt.preventDefault();
		},
		[ deleteSelectedElements, selectedElements ],
	);

	const pasteHandler = useCallback(
		( evt ) => {
			const { clipboardData } = evt;

			try {
				const html = clipboardData.getData( 'text/html' );
				if ( html ) {
					const template = document.createElement( 'template' );
					template.innerHTML = html;
					for ( let n = template.content.firstChild; n; n = n.nextSibling ) {
						if ( n.nodeType === /* COMMENT */ 8 ) {
							const payload = JSON.parse( n.nodeValue.replace( new RegExp( DOUBLE_DASH_ESCAPE, 'g' ), '--' ) );
							if ( payload.sentinel === 'story-elements' ) {
								payload.items.forEach( ( { x, y, basedOn, ...rest } ) => {
									currentPage.elements.forEach( ( element ) => {
										if ( element.id === basedOn || element.basedOn === basedOn ) {
											x = Math.max( x, element.x + 20 );
											y = Math.max( y, element.y + 20 );
										}
									} );
									const element = {
										...rest,
										basedOn,
										id: uuid(),
										x,
										y,
									};
									appendElementToCurrentPage( element );
								} );
								evt.preventDefault();
							}
						}
					}
				}
			} catch ( e ) {
				// Ignore.
			}
		},
		[ appendElementToCurrentPage, currentPage ],
	);

	useClipboardHandlers( container, copyCutHandler, pasteHandler );

	// @todo: return copy/cut/pasteAction that can be used in the context menus.
}

export default useCanvasSelectionCopyPaste;
