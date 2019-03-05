/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button, Tooltip } from '@wordpress/components';

export default function Indicator( { pages, currentPage, onClick, disabled } ) {
	/* translators: %s: Page number */
	const label = ( pageNumber ) => sprintf( __( 'Page %s', 'amp' ), pageNumber );

	/* translators: %s: Page number */
	const toolTip = ( pageNumber ) => sprintf( __( 'Go to page %s', 'amp' ), pageNumber );

	return (
		<ul className="amp-story-editor-carousel-item-list">
			{ pages.map( ( page, index ) => {
				const className = page.clientId === currentPage ? 'amp-story-editor-carousel-item amp-story-editor-carousel-item--active' : 'amp-story-editor-carousel-item';

				return (
					<li key={ page.clientId } className={ className }>
						<Tooltip text={ toolTip( index + 1 ) }>
							<Button
								onClick={ ( e ) => {
									e.preventDefault();
									onClick( page.clientId );
								} }
								disabled={ page.clientId === currentPage || disabled }
							>
								<span className="screen-reader-text">
									{ label( index + 1 ) }
								</span>
							</Button>
						</Tooltip>
					</li>
				);
			} ) }
		</ul>
	);
}
