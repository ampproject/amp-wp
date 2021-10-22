/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { Loading } from '../loading';
import { AMPNotice, NOTICE_TYPE_PLAIN, NOTICE_SIZE_SMALL } from '../amp-notice';

/**
 * Site Scan sources list component.
 *
 * @param {Object} props                         Component props.
 * @param {Array}  props.sources                 Sources data.
 * @param {string} props.inactiveSourceNotice    Message to show next to an inactive source.
 * @param {string} props.uninstalledSourceNotice Message to show next to an uninstalled source.
 */
export function SiteScanSourcesList( {
	sources,
	inactiveSourceNotice,
	uninstalledSourceNotice,
} ) {
	if ( sources.length === 0 ) {
		return <Loading />;
	}

	return (
		<ul className="site-scan-results__sources">
			{ sources.map( ( { author, name, slug, status, version } ) => (
				<li
					key={ slug }
					className="site-scan-results__source"
				>
					{ name && (
						<span className={ classnames( 'site-scan-results__source-name', {
							'site-scan-results__source-name--inactive': [ 'inactive', 'uninstalled' ].includes( status ),
						} ) }>
							{ name }
						</span>
					) }
					{ ! name && slug && (
						<code className="site-scan-results__source-slug">
							{ slug }
						</code>
					) }
					{ status === 'active' ? (
						<>
							{ author && (
								<span className="site-scan-results__source-author">
									{ sprintf(
										// translators: %s is an author name.
										__( 'by %s', 'amp' ),
										author,
									) }
								</span>
							) }
							{ version && (
								<span className="site-scan-results__source-version">
									{ sprintf(
										// translators: %s is a version number.
										__( 'Version %s', 'amp' ),
										version,
									) }
								</span>
							) }
						</>
					) : (
						<AMPNotice
							className="site-scan-results__source-notice"
							type={ NOTICE_TYPE_PLAIN }
							size={ NOTICE_SIZE_SMALL }
						>
							{ status === 'inactive' ? inactiveSourceNotice : null }
							{ status === 'uninstalled' ? uninstalledSourceNotice : null }
						</AMPNotice>
					) }
				</li>
			) ) }
		</ul>
	);
}

SiteScanSourcesList.propTypes = {
	sources: PropTypes.array.isRequired,
	inactiveSourceNotice: PropTypes.string,
	uninstalledSourceNotice: PropTypes.string,
};
