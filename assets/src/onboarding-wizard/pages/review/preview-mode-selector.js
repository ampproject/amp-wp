/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Gets the title for the preview mode selector.
 *
 * @param {string} mode The mode.
 */
function getTitle( mode ) {
	switch ( mode ) {
		case 'amp':
			return __( 'AMP', 'amp' );

		case 'non-amp':
			return __( 'Non-AMP', 'amp' );

		default:
			return null;
	}
}

/**
 * Preview mode selector.
 *
 * @param {Object}   props              Component props.
 * @param {Object}   props.modes        Preview modes.
 * @param {Function} props.onChange     Preview mode change handler.
 * @param {string}   props.selectedMode Currently selected mode.
 */
export function PreviewModeSelector( { modes, onChange, selectedMode } ) {
	return (
		<form className="review__preview-mode-selector">
			{ modes.map( ( mode ) => (
				<label
					key={ mode }
					className={ `review__preview-mode-selector-label ${ selectedMode === mode ? 'is-selected' : '' }` }
					htmlFor={ `preview-mode-${ mode }` }
				>
					<div className="review__preview-mode-selector-input">
						<input
							type="radio"
							id={ `preview-mode-${ mode }` }
							checked={ selectedMode === mode }
							onChange={ () => onChange( mode ) }
						/>
					</div>
					<h3 className="review__preview-mode-selector-title">
						{ getTitle( mode ) }
					</h3>
				</label>
			) ) }
		</form>
	);
}

PreviewModeSelector.propTypes = {
	modes: PropTypes.array,
	onChange: PropTypes.func,
	selectedMode: PropTypes.string,
};
