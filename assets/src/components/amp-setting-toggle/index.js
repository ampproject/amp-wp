/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Styled toggle control.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.checked Whether the toggle is on.
 * @param {Function} props.onChange Change handler.
 * @param {?string} props.text Toggle text.
 * @param {string} props.title Toggle title.
 */
export function AMPSettingToggle( { checked, onChange, text, title } ) {
	return (
		<div className="amp-setting-toggle">
			<ToggleControl
				checked={ checked }
				disabled={ true }
				label={ (
					<div className="amp-setting-toggle__label-text">
						<h3>
							{ title }
						</h3>
						{ text && (
							<p>
								{ text }
							</p> ) }
					</div>
				) }
				onChange={ onChange }
			/>
		</div>
	);
}
AMPSettingToggle.propTypes = {
	checked: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	text: PropTypes.string,
	title: PropTypes.string.isRequired,
};
