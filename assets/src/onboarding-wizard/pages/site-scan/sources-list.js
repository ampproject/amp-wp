/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Sources list component.
 *
 * @param {Object} props         Component props.
 * @param {Array}  props.sources Sources data.
 */
export function SourcesList( { sources } ) {
	return (
		<ul className="site-scan__sources">
			{ sources.map( ( { name, author, version } ) => (
				<li
					key={ name }
					className="site-scan__source"
				>
					<span className="site-scan__source-name">
						{ name }
					</span>
					{ author && (
						<span className="site-scan__source-author">
							{ sprintf(
								// translators: %s is an author name.
								__( 'by %s', 'amp' ),
								author,
							) }
						</span>
					) }
					{ version && (
						<span className="site-scan__source-version">
							{ sprintf(
								// translators: %s is a version number.
								__( 'Version %s', 'amp' ),
								version,
							) }
						</span>
					) }
				</li>
			) ) }
		</ul>
	);
}

SourcesList.propTypes = {
	sources: PropTypes.array.isRequired,
};
