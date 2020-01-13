/**
 * External dependencies
 */
import styled from 'styled-components';
/**
 * Internal dependencies
 */
import useInspector from './useInspector';
import DesignInspector from './designInspector';
import DocumentInspector from './documentInspector';
import PrepublishInspector from './prepublishInspector';
import { getTabId } from './shared';

const InspectorWrapper = styled.div.attrs( { tabIndex: '0', role: 'tabpanel' } )``;
const InspectorForm = styled.form``;

function Inspector() {
	const {
		state: { tab },
		data: { tabs: { DESIGN, DOCUMENT, PREPUBLISH } },
	} = useInspector();

	const ContentInspector = ( {
		[ DESIGN ]: DesignInspector,
		[ DOCUMENT ]: DocumentInspector,
		[ PREPUBLISH ]: PrepublishInspector,
	} )[ tab ];

	return (
		<InspectorWrapper aria-labelledby={ tab } id={ getTabId( tab ) }>
			<InspectorForm>
				<ContentInspector />
			</InspectorForm>
		</InspectorWrapper>
	);
}

export default Inspector;
