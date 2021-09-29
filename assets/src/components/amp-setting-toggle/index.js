/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';
import { isValidElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Styled toggle control.
 *
 * @param {Object}        props          Component props.
 * @param {boolean}       props.checked  Whether the toggle is on.
 * @param {boolean}       props.compact  Whether the toggle is compact/small.
 * @param {boolean}       props.disabled Whether the toggle is disabled.
 * @param {Function}      props.onChange Change handler.
 * @param {string}        props.text     Toggle text.
 * @param {Object|string} props.title    Toggle title.
 */
export function AMPSettingToggle( {
	checked,
	compact = false,
	disabled = false,
	onChange,
	text,
	title,
} ) {
	return (
		<div
			className={ classnames( 'amp-setting-toggle', {
				'amp-setting-toggle--disabled': disabled,
				'amp-setting-toggle--compact': compact,
			} ) }
		>
			<ToggleControl
				checked={ ! disabled && checked }
				label={ (
					<div className="amp-setting-toggle__label-text">
						{
							title && (
								isValidElement( title )
									? title
									: (
										<h3>
											{ title }
										</h3>
									)
							)
						}
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
	compact: PropTypes.bool,
	disabled: PropTypes.bool,
	onChange: PropTypes.func.isRequired,
	text: PropTypes.string,
	title: PropTypes.node,
};
