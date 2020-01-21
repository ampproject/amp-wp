/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Helper to generate tab html ID.
 *
 * @param {string}id ID as string
 * @return {string} Formatted ID.
 */
const getTabId = ( id ) => {
	return `${ id }-tab`;
};

const Label = styled.span`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 11px;
	line-height: 16px;
	width: 80px;
`;

const Group = styled.label`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	display: block;
	align-items: center;
	margin-bottom: 10px;
	opacity: ${ ( { disabled } ) => disabled ? 0.7 : 1 };
`;

const Select = styled.select`
	width: 100%;
`;

const Input = styled.input`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	border: 1px solid;
	border-radius: 4px;
	font-size: 11px;
	line-height: 16px;
	width: 100%;
`;

function SelectMenu( { label, name, options, value, onChange, postfix, disabled } ) {
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Select
				disabled={ disabled }
				value={ value }
				onChange={ ( evt ) => onChange( evt.target.value ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
			>
				{ options && options.map( ( { name: optionName, value: optionValue } ) => (
					<option key={ `${ name }-${ optionValue }` } value={ optionValue } dangerouslySetInnerHTML={ { __html: optionName } } />
				) ) }
			</Select>
			{ postfix }
		</Group>
	);
}

SelectMenu.propTypes = {
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	options: PropTypes.array.isRequired,
	onChange: PropTypes.func.isRequired,
	postfix: PropTypes.string,
	disabled: PropTypes.bool,
};

SelectMenu.defaultProps = {
	postfix: '',
	formName: '',
	disabled: false,
};

function InputGroup( { type, label, value, isMultiple, onChange, postfix, disabled } ) {
	const placeholder = isMultiple ? __( '( multiple )', 'amp' ) : '';
	const isCheckbox = type === 'checkbox';
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Input
				type={ type || 'number' }
				disabled={ disabled }
				onChange={ ( evt ) => onChange( isCheckbox ? evt.target.checked : evt.target.value ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
				placeholder={ placeholder }
				value={ isCheckbox ? '' : value }
				checked={ isCheckbox ? value : null }
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
	type: 'text',
	postfix: '',
	disabled: false,
	isMultiple: false,
};

export {
	getTabId,
	SelectMenu,
	InputGroup,
};
