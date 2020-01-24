/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Label from './label';
import Group from './group';

const Select = styled.select`
	width: 100px;
`;

function SelectMenu( { label, options, value, isMultiple, onChange, postfix, disabled } ) {
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Select
				disabled={ disabled }
				value={ value }
				onChange={ ( evt ) => onChange( evt.target.value, evt ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
			>
				{ isMultiple ? ( <option dangerouslySetInnerHTML={ { __html: __( '( multiple )', 'amp' ) } } /> ) :
					options && options.map( ( { name, slug, thisValue } ) => (
						<option key={ slug } value={ thisValue } dangerouslySetInnerHTML={ { __html: name } } />
					) ) }
			</Select>
			{ postfix }
		</Group>
	);
}

SelectMenu.propTypes = {
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	isMultiple: PropTypes.bool,
	options: PropTypes.array.isRequired,
	onChange: PropTypes.func.isRequired,
	postfix: PropTypes.string,
	disabled: PropTypes.bool,
};

SelectMenu.defaultProps = {
	postfix: '',
	disabled: false,
	isMultiple: false,
};

export default SelectMenu;
