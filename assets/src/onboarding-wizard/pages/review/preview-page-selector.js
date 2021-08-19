/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Selectable } from '../../../components/selectable';

/**
 * Gets the title for the preview page selector.
 *
 * @param {string} page The page type.
 */
function getTitle( page ) {
	switch ( page ) {
		case 'home':
			return __( 'Homepage', 'amp' );

		case 'author':
			return __( 'Author page', 'amp' );

		case 'date':
			return __( 'Archive page', 'amp' );

		case 'search':
			return __( 'Search results', 'amp' );

		default:
			return `${ page.charAt( 0 ).toUpperCase() }${ page.slice( 1 ) }`;
	}
}

/**
 * Preview page selector.
 *
 * @param {Object}   props              Component props.
 * @param {Array}    props.pages        Preview pages.
 * @param {Function} props.onChange     Preview page change handler.
 * @param {string}   props.selectedPage Currently selected page.
 */
export function PreviewPageSelector( { pages, onChange, selectedPage } ) {
	return (
		<Selectable>
			<form className="review__preview-page-selector">
				{ pages.map( ( page ) => (
					<label
						key={ page }
						className={ `review__preview-page-selector-label ${ selectedPage === page ? 'is-selected' : '' }` }
						htmlFor={ `preview-${ page }` }
					>
						<div className="review__preview-page-selector-input">
							<input
								type="radio"
								id={ `preview-${ page }` }
								checked={ selectedPage === page }
								onChange={ () => onChange( page ) }
							/>
						</div>
						<h3 className="review__preview-page-selector-title">
							{ getTitle( page ) }
						</h3>
					</label>
				) ) }
			</form>
		</Selectable>
	);
}

PreviewPageSelector.propTypes = {
	pages: PropTypes.array,
	onChange: PropTypes.func,
	selectedPage: PropTypes.string,
};
