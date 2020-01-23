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

const Input = styled.input`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	border: 1px solid;
	border-radius: 4px;
	font-size: 11px;
	line-height: 16px;
	width: 100px;
`;

function InputGroup( { type, label, value, isMultiple, onChange, postfix, disabled, ...rest } ) {
	const placeholder = isMultiple ? __( '( multiple )', 'amp' ) : '';
	const isCheckbox = type === 'checkbox';
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Input
				type={ type }
				disabled={ disabled }
				onChange={ ( evt ) => onChange( isCheckbox ? evt.target.checked : evt.target.value, evt ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
				placeholder={ placeholder }
				value={ isCheckbox ? 'on' : value }
				checked={ isCheckbox ? value : null }
				{ ...rest }
			/>
			{ postfix }
		</Group>
	);
}

InputGroup.propTypes = {
	type: PropTypes.string,
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	isMultiple: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	postfix: PropTypes.string,
	disabled: PropTypes.bool,
};

InputGroup.defaultProps = {
	type: 'number',
	postfix: '',
	disabled: false,
};

export default InputGroup;
