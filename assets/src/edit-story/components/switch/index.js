/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';

const Group = styled.label`
	display: flex;
	align-items: center;
`;

const Space = styled.div`
	width: 6px;
`;

const Label = styled.span`
	margin-right: 6px;
	font-size: 11px;
	text-transform: uppercase;
`;

const Checkbox = styled.input.attrs( { type: 'checkbox' } )`
	// Hide checkbox visually but remain accessible to screen readers.
  // Source: https://polished.js.org/docs/#hidevisually
  border: 0;
  clip: rect(0 0 0 0);
  clippath: inset(50%);
  height: 1px;
  margin: -1px;
  overflow: hidden;
  padding: 0;
  position: absolute;
  white-space: nowrap;
  width: 1px;
`;

const Slider = styled.span`
	display: block;
	width: 24px;
	height: 14px;
	position: relative;
	border-radius: 6px;
	border: 2px solid ${ ( { theme } ) => theme.colors.fg.v3 };
	transition: border-color .2s ease;

	&::after {
		content: '';
		display: block;
		position: absolute;
		left: 3px;
		top: 2px;
		width: 6px;
		height: 6px;
		border-radius: 3px;
		background-color: ${ ( { theme } ) => theme.colors.fg.v3 };
		transition: border-color .2s ease;
		transition-property: border-color, left, width;
	}

	${ Checkbox }:checked + & {
		border-color: ${ ( { theme } ) => theme.colors.action };
		&::after {
			background-color: ${ ( { theme } ) => theme.colors.action };
			left: 11px;
		}
	}

	${ Checkbox }:active + &::after {
		width: 9px;
	}

	${ Checkbox }:active:checked + &::after {
		left: 8px;
	}
`;

function Switch( { label, checked, onChange } ) {
	const [ on, setOn ] = useState( checked );
	const handleChange = ( evt ) => {
		setOn( evt.target.checked );
		if ( onChange ) {
			onChange( evt.target.checked );
		}
	};
	useEffect( () => setOn( checked ), [ checked ] );
	return (
		<Group>
			<Label>
				{ label }
			</Label>
			<Space />
			<Checkbox checked={ on } onChange={ handleChange } />
			<Slider />
		</Group>
	);
}

Switch.propTypes = {
	label: PropTypes.string.isRequired,
	checked: PropTypes.bool,
	onChange: PropTypes.func,
};

Switch.defaultProps = {
	checked: false,
};

export default Switch;
