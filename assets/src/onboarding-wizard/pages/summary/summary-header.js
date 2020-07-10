/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export function SummaryHeader( { illustration, title, text } ) {
	return (
		<div className="summary-header selectable selectable--bottom">

			<div className="summary-header__header">
				<div className="summary-header__illustration">
					{ illustration }
				</div>
				<h2>
					{ title }
				</h2>
			</div>

			<div className="summary-header__body">
				<div>
					<p dangerouslySetInnerHTML={ { __html: text } } />
				</div>
			</div>
		</div>
	);
}

SummaryHeader.propTypes = {
	illustration: PropTypes.node.isRequired,
	title: PropTypes.string.isRequired,
	text: PropTypes.string.isRequired,
};
