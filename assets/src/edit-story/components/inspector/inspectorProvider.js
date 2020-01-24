/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI } from '../../app/api';
import Context from './context';

const DESIGN = 'design';
const DOCUMENT = 'document';
const PREPUBLISH = 'prepublish';

function InspectorProvider( { children } ) {
	const { actions: { getAllStatuses, getAllUsers } } = useAPI();
	const [ tab, setTab ] = useState( DESIGN );
	const [ users, setUsers ] = useState( [] );
	const [ statuses, setStatuses ] = useState( [] );
	const [ inspectorContentHeight, setInspectorContentHeight ] = useState( null );

	const [ isUsersLoading, setIsUsersLoading ] = useState( false );
	const [ isStatusesLoading, setIsStatusesLoading ] = useState( false );

	// TODO Implement `useResizeObserver` once either branch is merged
	const setInspectorContentNode = useCallback( ( node ) => {
		setInspectorContentHeight( node.clientHeight );
	}, [] );

	const loadStatuses = useCallback( () => {
		if ( ! isStatusesLoading && statuses.length === 0 ) {
			setIsStatusesLoading( true );
			getAllStatuses().then( ( data ) => {
				data = Object.values( data );
				data = data.filter( ( { show_in_list: isShown } ) => isShown );
				const saveData = data.map( ( {
					slug,
					name,
				} ) => ( {
					value: slug,
					name,
				} ) );
				setStatuses( saveData );
				setIsStatusesLoading( false );
			} );
		}
	}, [ isStatusesLoading, statuses.length, getAllStatuses ] );

	const loadUsers = useCallback( () => {
		if ( ! isUsersLoading && users.length === 0 ) {
			setIsUsersLoading( true );
			getAllUsers().then( ( data ) => {
				const saveData = data.map( ( {
					id,
					name,
				} ) => ( {
					value: id,
					name,
				} ) );

				setUsers( saveData );
				setIsUsersLoading( false );
			} );
		}
	}, [ isUsersLoading, users.length, getAllUsers ] );

	const state = {
		state: {
			tab,
			users,
			statuses,
			inspectorContentHeight,
		},
		actions: {
			setTab,
			loadStatuses,
			loadUsers,
			setInspectorContentNode,
		},
		data: {
			tabs: {
				DESIGN,
				DOCUMENT,
				PREPUBLISH,
			},
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

InspectorProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default InspectorProvider;
