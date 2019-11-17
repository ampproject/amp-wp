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
	justify-content: space-between;
	padding: 1em;
	height: 100%;
`;

const List = styled.div`
	display: flex;
`;

const Space = styled.div`
	width: 1em;
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
			<List>
				<Undoer />
				<Space />
				<Redoer />
			</List>
			<List>
				<Outline>
					{ __( 'Preview' ) }
				</Outline>
				<Space />
				<Primary>
					{ __( 'Publish' ) }
				</Primary>
				<Space />
			</List>
		</ButtonList>
	);
}
export default Buttons;

