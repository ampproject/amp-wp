/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import useInspector from './useInspector';
import InspectorTabs from './inspectorTabs';
import InspectorContent from './inspectorContent';

const Layout = styled.div`
	height: 100%;
	display: grid;
	grid:
		"tabs   " 40px
		"inspector" 1fr
		/ 1fr;
`;

const TabsArea = styled.div`
	grid-area: tabs
`;

const InspectorBackground = styled.div`
	grid-area: inspector;
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	height: 100%;
	padding: 0;
	color: ${ ( { theme } ) => theme.colors.bg.v4 };
	overflow: auto;
`;

function InspectorLayout() {
	const { actions: { setInspectorContentNode } } = useInspector();
	return (
		<Layout>
			<TabsArea>
				<InspectorTabs />
			</TabsArea>
			<InspectorBackground ref={ setInspectorContentNode }>
				<InspectorContent />
			</InspectorBackground>
		</Layout>
	);
}

export default InspectorLayout;
