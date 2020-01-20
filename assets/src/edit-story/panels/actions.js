/**
 * NOTE: This is temporary panel for being able to remove elements.
 * It will be removed and replaced by using keyboard "Delete"
 * once the approach for keyboard events has been confirmed.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { SimplePanel } from './panel';

const Delete = styled.a`
	cursor: pointer;
	color: ${ ( { theme } ) => theme.colors.action };

	&:hover {
		color: ${ ( { theme } ) => theme.colors.danger };
	}
`;

function ActionsPanel( { deleteSelectedElements } ) {
	return (
		<SimplePanel title="Actions">
			<Delete onClick={ deleteSelectedElements } >
				{ 'Remove element' }
			</Delete>
		</SimplePanel>
	);
}

ActionsPanel.propTypes = {
	deleteSelectedElements: PropTypes.func.isRequired,
};

export default ActionsPanel;
