/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { withState } from '@wordpress/compose';
import { Draggable, DropZoneProvider, DropZone } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

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
					const pageElementId = `reorder-page-${ clientId }`;
					const transferData = {
						type: 'block',
						srcIndex: index,
						srcRootClientId: '',
						srcClientId: clientId,
					};

					return (
						<div
							key={ `page-${ clientId }` }
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
												label={ __( 'Drop page to re-order', 'amp' ) }
												onDrop={ () => setState( { hasDropped: true } ) }
											/>
											<div id={ pageElementId }>
												<div
													className="amp-story-page-preview"
													onDragStart={ onDraggableStart }
													onDragEnd={ onDraggableEnd }
													draggable
												>
													<BlockPreview { ...page } />
												</div>
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
