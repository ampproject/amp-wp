/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Radio group.
 *
 * @param {Object}   props              Component props.
 * @param {Array}    props.options      List of option values and titles.
 * @param {Function} props.onChange     Change handler.
 * @param {string}   props.selected     Currently selected option.
 * @param {boolean}  props.isHorizontal Flag indicating that the options should not be stacked.
 */
export function RadioGroup( { options = [], onChange, selected, isHorizontal } ) {
	const htmlIdPrefix = `radio-group-${ useInstanceId( RadioGroup ) }`;

	return (
		<form
			className={ classnames( 'radio-group', {
				'radio-group--stacked': ! isHorizontal,
				'radio-group--horizontal': isHorizontal,
			} ) }
		>
			{ options.map( ( { value, title } ) => (
				<label
					key={ value }
					className={ classnames( 'radio-group__label', {
						'radio-group__label--selected': isHorizontal,
					} ) }
					htmlFor={ `${ htmlIdPrefix }-${ value }` }
				>
					<div className="radio-group__input">
						<input
							type="radio"
							id={ `${ htmlIdPrefix }-${ value }` }
							checked={ selected === value }
							onChange={ () => onChange( value ) }
						/>
					</div>
					<h3 className="radio-group__title">
						{ title }
					</h3>
				</label>
			) ) }
		</form>
	);
}

RadioGroup.propTypes = {
	options: PropTypes.arrayOf(
		PropTypes.shape( {
			value: PropTypes.string,
			title: PropTypes.string,
		} ),
	),
	onChange: PropTypes.func,
	selected: PropTypes.string,
	isHorizontal: PropTypes.bool,
};
