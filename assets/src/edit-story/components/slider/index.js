/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

const Group = styled.label`
	display: flex;
	align-items: center;
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
		left: 1px;
		top: 2px;
		width: 6px;
		height: 6px;
		border-radius: 50%;
		background-color: ${ ( { theme } ) => theme.colors.fg.v3 };
		transition: border-color .2s ease;
		transition-property: border-color, left;
	}

	${ Checkbox }:checked + & {
		border-color: ${ ( { theme } ) => theme.colors.action };
		&::after {
			background-color: ${ ( { theme } ) => theme.colors.action };
			left: 13px;
		}
	}
`;

function Switch( { label } ) {
	const [ on, setOn ] = useState( false );
	return (
		<Group>
			<Label>
				{ label }
			</Label>
			<Checkbox checked={ on } onChange={ ( evt ) => setOn( evt.target.checked ) } />
			<Slider />
		</Group>
	);
}

export default Switch;
