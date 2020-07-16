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
 * @param {boolean} props.disabled Whether the toggle is disabled.
 * @param {Function} props.onChange Change handler.
 * @param {?string} props.text Toggle text.
 * @param {string} props.title Toggle title.
 * @param {number} props.headingLevel Heading level for title.
 */
export function AMPSettingToggle( { checked, disabled = false, onChange, text, title, headingLevel } ) {
	const Heading = headingLevel ? `h${ headingLevel }` : 'h3';

	return (
		<div className={ `amp-setting-toggle ${ disabled ? 'amp-setting-toggle--disabled' : '' }` }>
			<ToggleControl
				checked={ ! disabled && checked }
				label={ (
					<div className="amp-setting-toggle__label-text">
						<Heading>
							{ title }
						</Heading>
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
	disabled: PropTypes.bool,
	onChange: PropTypes.func.isRequired,
	text: PropTypes.string,
	headingLevel: PropTypes.number,
	title: PropTypes.string.isRequired,
};
