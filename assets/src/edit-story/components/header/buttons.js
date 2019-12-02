/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI, useHistory, useStory } from '../../app';
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
	const { actions: { saveStoryById } } = useAPI();
	const {
		state: { storyId, title, postStatus, pages },
		actions: { setPostStatus },
	} = useStory();
	const [ isSaving, setIsSaving ] = useState( false );

	const status = ( postStatus !== 'publish' ) ? 'publish' : postStatus;
	const text = ( postStatus !== 'publish' ) ? __( 'Publish' ) : __( 'Update' );

	const savePost = useCallback( () => {
		if ( ! isSaving ) {
			setIsSaving( true );
			saveStoryById( storyId, title, status, pages ).then( () => {
				setIsSaving( false );
				setPostStatus( status );
			} );
		}
	}, [ isSaving, saveStoryById, storyId, title, status, pages, setPostStatus ] );

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

