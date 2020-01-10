/**
 * Internal dependencies
 */
/**
 * External dependencies
 */
import styled from 'styled-components';
import useInspector from './useInspector';

const Tabs = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	display: flex;
	height: 100%;
	margin: 0;
`;

const Tab = styled.button`
	width: 33.33%;
	line-height: 40px;
	height: 100%;
	text-align: center;
	cursor: pointer;
	border: 0px none;
	background: none;
	text-transform: uppercase;
	color: ${ ( { theme } ) => theme.colors.bg.v4 };
	
	font-weight: ${ ( { isActive } ) => isActive ? 'bold' : 'normal;' };;
	${ ( { isActive } ) => ! isActive && `
		opacity: .4;
		&:hover { opacity: 1; }
	` }
	&:focus, &:active {
		outline: none;
	}
`;

function InspectorTabs() {
	const {
		state: { tab },
		actions: { setTab },
		data: { tabs: { DESIGN, DOCUMENT, PREPUBLISH } },
	} = useInspector();
	const tabs = [
		[ DESIGN, 'Design' ],
		[ DOCUMENT, 'Document' ],
		[ PREPUBLISH, 'Prepublish' ],
	];
	return (
		<Tabs>
			{ tabs.map( ( [ id, Text ] ) => (
				<Tab key={ id } id={ id } isActive={ tab === id } aria-controls={ `${ id }-tab` } role="tab" aria-selected={ tab === id } onClick={ () => setTab( id ) }>
					{ Text }
				</Tab>
			) ) }
		</Tabs>
	);
}

export default InspectorTabs;
