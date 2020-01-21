/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */

const Label = styled.span`
	color: ${ ( { theme } ) => theme.colors.fg.v5 };
	font-size: 15px;
  line-height: 16px;
  text-align: center;
	width: 35px;
`;

const Input = styled.input`
  color: ${ ( { theme } ) => theme.colors.fg.v6 };
  border: 0 !important;
	font-size: 15px;
	line-height: 16px;
  width: 63px;
  padding: 0 !important;
  margin: 0;
  
  :focus {
    outline: none !important;
    box-shadow: none !important;
  }
`;

const Group = styled.label`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
  display: flex;
  align-items: center;
  border: 1px solid ${ ( { theme } ) => theme.colors.fg.v3 };
  border-radius: 4px;
  width: 100px;
	margin-bottom: 5px;
	opacity: ${ ( { disabled } ) => disabled ? 0.7 : 1 };
`;

function PrefixInput( { type, label, value, isMultiple, onChange, disabled, min, max } ) {
	const placeholder = isMultiple ? '( multiple )' : '';
	return (
		<Group disabled={ disabled }>
			<Label>
				{ label }
			</Label>
			<Input
				type={ type || 'number' }
				disabled={ disabled }
				onChange={ ( evt ) => onChange( evt.target.value, evt ) }
				onBlur={ ( evt ) => evt.target.form.dispatchEvent( new window.Event( 'submit' ) ) }
				placeholder={ placeholder }
				value={ value }
				min={ min ? min : null }
				max={ max ? max : null }
			/>
		</Group>
	);
}

PrefixInput.propTypes = {
	type: PropTypes.string,
	label: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	isMultiple: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	disabled: PropTypes.bool,
	min: PropTypes.any,
	max: PropTypes.any,
};

PrefixInput.defaultProps = {
	type: 'number',
	disabled: false,
	min: null,
	max: null,
};

export default PrefixInput;
