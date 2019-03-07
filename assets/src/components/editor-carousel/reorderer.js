/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { withState } from '@wordpress/compose';
import { Draggable, DropZoneProvider, DropZone } from '@wordpress/components';

/**
 * Internal dependencies
 */
import BlockPreview from './block-preview';

const Reorderer = ( { pages, dragging, hasDropped, setState } ) => {
	return (
		<DropZoneProvider>
			{ hasDropped ? 'Dropped!' : 'Drop something here' }
			<div className="amp-story-reorderer">
				{ pages.map( ( page, index ) => {
					const { clientId } = page;
					const pageElementId = `page-${ clientId }`;
					const transferData = {
						type: 'block',
						srcIndex: index,
						srcRootClientId: '',
						srcClientId: clientId,
					};

					return (
						<div
							key={ pageElementId }
							className="amp-story-reorderer-item"
						>
							<Draggable
								className={ dragging ? 'is-dragging' : undefined }
								elementId={ pageElementId }
								transferData={ transferData }
								onDragStart={ () => {
									setState( { dragging: true } );
								} }
								onDragEnd={ () => {
									setState( { dragging: false } );
								} }
							>
								{
									( { onDraggableStart, onDraggableEnd } ) => (
										<Fragment>
											<DropZone
												onDrop={ () => setState( { hasDropped: true } ) }
											/>
											<div
												className="amp-story-page-preview"
												onDragStart={ onDraggableStart }
												onDragEnd={ onDraggableEnd }
												draggable
											>
												<BlockPreview { ...page } />
											</div>
										</Fragment>
									)
								}
							</Draggable>
						</div>
					);
				} ) }
			</div>
		</DropZoneProvider>
	);
};

export default withState( {
	dragging: false,
	hasDropped: false,
} )( Reorderer );
