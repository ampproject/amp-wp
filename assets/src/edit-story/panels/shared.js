/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

export const Panel = styled.form`
	display: flex;
	flex-direction: column;
`;

export const Title = styled.h2`
	color: ${ ( { theme } ) => theme.colors.bg.v2 };
	font-size: 13px;
	line-height: 19px;
`;

const Label = styled.span`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 11px;
	line-height: 16px;
	width: 80px;
`;

const Input = styled.input`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	border: 1px solid;
	border-radius: 4px;
	font-size: 11px;
	line-height: 16px;
	width: 100px;
`;

const Group = styled.label`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	display: flex;
	align-items: center;
	margin-bottom: 5px;
	opacity: ${ ( { disabled } ) => disabled ? 0.7 : 1 };
`;

export const ActionButton = styled.button`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 11px;
`;

function InputGroup( { type, label, value, isMultiple, onChange, postfix, disabled } ) {
	const placeholder = isMultiple ? '( multiple )' : '';
	const isCheckbox = type === 'checkbox';
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Input
				type={ type || 'number' }
				disabled={ disabled }
				onChange={ ( evt ) => onChange( isCheckbox ? evt.target.checked : evt.target.value, evt ) }
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
	type: 'number',
	postfix: '',
	disabled: false,
};

function SelectMenu( { label, options, value, onChange, postfix, disabled } ) {
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<select
				style={ { width: 100 } }
				disabled={ disabled }
				value={ value }
				onChange={ ( evt ) => onChange( evt.target.value, evt ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
			>
				{ options && options.map( ( { name, slug } ) => (
					<option key={ slug } value={ name }>
						{ name }
					</option>
				) ) }
			</select>
			{ postfix }
		</Group>
	);
}

SelectMenu.propTypes = {
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	options: PropTypes.array.isRequired,
	onChange: PropTypes.func.isRequired,
	postfix: PropTypes.string,
	disabled: PropTypes.bool,
};

SelectMenu.defaultProps = {
	postfix: '',
	disabled: false,
};

function getCommonValue( list, property ) {
	const first = list[ 0 ][ property ];
	const allMatch = list.every( ( el ) => el[ property ] === first );
	return allMatch ? first : '';
}

export {
	InputGroup,
	getCommonValue,
	SelectMenu,
};

