/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import Panel from './panel';
import PanelTitle from './title';
import PanelContent from './content';

function SimplePanel( { children, title, onSubmit } ) {
	return (
		<Panel>
			<PanelTitle>
				{ title }
			</PanelTitle>
			<PanelContent onSubmit={ onSubmit }>
				{ children }
			</PanelContent>
		</Panel>
	);
}

SimplePanel.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	title: PropTypes.string.isRequired,
	onSubmit: PropTypes.func,
};

export default SimplePanel;
