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

function InputGroup( { label, value, isMultiple, onChange, postfix, disabled } ) {
	const placeholder = isMultiple ? '( multiple )' : '';
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Input
				disabled={ disabled }
				onChange={ ( evt ) => onChange( evt.target.value, evt ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
				placeholder={ placeholder }
				value={ value }
			/>
			{ postfix }
		</Group>
	);
}

InputGroup.propTypes = {
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	isMultiple: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	postfix: PropTypes.string,
	disabled: PropTypes.bool,
};

InputGroup.defaultProps = {
	postfix: '',
};

function getCommonValue( list, property ) {
	const first = list[ 0 ][ property ];
	const allMatch = list.every( ( el ) => el[ property ] === first );
	return allMatch ? first : '';
}

export {
	InputGroup,
	getCommonValue,
};

