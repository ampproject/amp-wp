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
 * @param {Object} props        Component props.
 * @param {Array}  props.issues List of sources causing issues.
 */
export function SourcesList( { issues } ) {
	return (
		<ul className="site-scan__sources">
			{ issues.map( ( slug ) => (
				<li
					key={ slug }
					className="site-scan__source"
				>
					<span className="site-scan__source-name">
						{ slug }
					</span>
					<span className="site-scan__source-author">
						{ sprintf(
							// translators: %s is an author name.
							__( 'by %s', 'amp' ),
							'Foo Bar',
						) }
					</span>
					<span className="site-scan__source-version">
						{ sprintf(
							// translators: %s is a version number.
							__( 'Version %s', 'amp' ),
							'1.0',
						) }
					</span>
				</li>
			) ) }
		</ul>
	);
}

SourcesList.propTypes = {
	issues: PropTypes.array.isRequired,
};
