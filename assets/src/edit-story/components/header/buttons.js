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
import { useHistory, useStory } from '../../app';
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

function Publish() {
	const {
		state: { isSaving, postStatus },
		actions: { savePost },
	} = useStory();

	const text = ( postStatus !== 'publish' ) ? __( 'Publish' ) : __( 'Update' );

	return (
		<Primary onClick={ savePost } isDisabled={ isSaving }>
			{ text }
		</Primary>
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
				<Publish />
				<Space />
			</List>
		</ButtonList>
	);
}
export default Buttons;

