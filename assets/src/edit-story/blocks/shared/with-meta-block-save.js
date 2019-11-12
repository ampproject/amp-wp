/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export default ( { tagName } ) => {
	const MetaBlockSave = () => {
		const className = '';
		const styles = { textAlign: 'center' };

		const ContentTag = tagName;

		return (
			<ContentTag
				style={ styles }
				className={ className }>
				<amp-fit-text layout="flex-item" className="amp-text-content">
					{ '{content}' }
				</amp-fit-text>
			</ContentTag>
		);
	};

	MetaBlockSave.propTypes = {
		attributes: PropTypes.shape( {
			ampFitText: PropTypes.bool,
		} ),
	};

	return MetaBlockSave;
};
