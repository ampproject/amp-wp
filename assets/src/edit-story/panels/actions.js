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
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Panel, Title } from './shared';

/**
 * WordPress dependencies
 */

const Delete = styled.a`
	cursor: pointer;
	color: ${ ( { theme } ) => theme.colors.action };

	&:hover {
		color: ${ ( { theme } ) => theme.colors.danger };
	}
`;

function ActionsPanel( { deleteSelectedElements } ) {
	return (
		<Panel>
			<Title>
				{ __( 'Actions', 'amp' ) }
			</Title>
			<Delete onClick={ deleteSelectedElements } >
				{ __( 'Remove element', 'amp' ) }
			</Delete>
		</Panel>
	);
}

ActionsPanel.propTypes = {
	deleteSelectedElements: PropTypes.func.isRequired,
};

export default ActionsPanel;
