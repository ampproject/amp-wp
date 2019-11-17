/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useHistory } from '../../app';
import { Outline, Primary, Undo, Redo } from '../button';

const ButtonList = styled.nav`
	background-color: ${ ( { theme } ) => theme.colors.bg.v3 };
	display: flex;
	justify-content: flex-end;
	padding: 1em;
	height: 100%;

	button {
		margin-left: 1em;
	}
`;

function Undoer() {
	const { state: { canUndo }, actions: { undo } } = useHistory();
	return (
		<Undo onClick={ undo } isDisabled={ ! canUndo } />
	);
}

function Redoer() {
	const { state: { canRedo }, actions: { redo } } = useHistory();
	return (
		<Redo onClick={ redo } isDisabled={ ! canRedo } />
	);
}

function Buttons() {
	return (
		<ButtonList>
			<Undoer />
			<Redoer />
			<Outline>
				{ __( 'Preview' ) }
			</Outline>
			<Primary>
				{ __( 'Publish' ) }
			</Primary>
		</ButtonList>
	);
}
export default Buttons;

