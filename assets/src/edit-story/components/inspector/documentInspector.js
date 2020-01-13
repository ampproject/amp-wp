/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { useStory } from '../../app/story';
import useInspector from './useInspector';
import { SelectMenu, InputGroup } from './shared';

function DocumentInspector() {
	const {
		actions: { loadStatuses, loadUsers, setDisabledStatuses },
		state: { users, statuses, disabledStatuses },
	} = useInspector();

	const {
		state: { meta: { isSaving }, story: { author, status, date, excerpt }, capabilities },
		actions: { updateStory },
	} = useStory();

	const [ state, setState ] = useState( { hasPublishAction: false, hasAssignAuthorAction: false } );



	useEffect( () => {
		loadStatuses();
		loadUsers();
	} );

	useEffect(() => {
		const { hasPublishAction, hasAssignAuthorAction } = capabilities;
		setState({ hasPublishAction, hasAssignAuthorAction });
	}, [capabilities, setState]);

	useEffect( () => {
		const states = ( status === 'future' ) ? [ 'pending' ] : [ 'future', 'pending' ];
		setDisabledStatuses( states );
	}, [ status, setDisabledStatuses ] );

	const handleChangeStatus = useCallback(
		( evt ) => {
			updateStory( { properties: { status: evt.target.value } } );
		}, [ updateStory ],
	);

	const handleChangeAuthor = useCallback(
		( evt ) => updateStory( { properties: { author: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeDate = useCallback(
		( value, evt ) => updateStory( { properties: { date: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeExcerpt = useCallback(
		( value, evt ) => updateStory( { properties: { excerpt: evt.target.value } } ),
		[ updateStory ],
	);

	return (
		<>
			<h2>
				{ 'Document' }
			</h2>
			{ state.hasPublishAction && statuses && <SelectMenu
				label="Status"
				name="status"
				options={ statuses }
				disabled={ isSaving }
				disabledOptions={ disabledStatuses }
				value={ status }
				onChange={ handleChangeStatus }
			/> }
			<InputGroup
				label={ 'Published date' }
				type={ 'datetime-local' }
				value={ date }
				disabled={ isSaving }
				onChange={ handleChangeDate }
			/>
			{ state.hasAssignAuthorAction && users && <SelectMenu
				label="Author"
				name="user"
				options={ users }
				value={ author }
				disabled={ isSaving }
				onChange={ handleChangeAuthor }
			/> }
			<InputGroup
				label={ 'Excerpt' }
				type={ 'text' }
				value={ excerpt }
				disabled={ isSaving }
				onChange={ handleChangeExcerpt }
			/>
		</>
	);
}

export default DocumentInspector;
