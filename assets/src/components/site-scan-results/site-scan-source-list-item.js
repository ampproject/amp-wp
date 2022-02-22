/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_PLAIN, NOTICE_SIZE_SMALL } from '../amp-notice';
import { SiteScanSourcesDetail } from './site-scan-sources-detail';

/**
 * Site Scan sources list item component.
 *
 * @param {Object} props                         Component props.
 * @param {string} props.author                  Plugin/Theme author.
 * @param {string} props.name                    Plugin/Theme name.
 * @param {string} props.slug                    Plugin/Theme slug.
 * @param {string} props.status                  Current status of Plugin/Theme.
 * @param {string} props.version                 Plugin/Theme version.
 * @param {string} props.inactiveSourceNotice    Message to show next to an inactive source.
 * @param {string} props.uninstalledSourceNotice Message to show next to an uninstalled source.
 */
export function SiteScanSourcesListItem( {
	author,
	name,
	slug,
	status,
	version,
	inactiveSourceNotice,
	uninstalledSourceNotice,
} ) {
	const [ hasOpened, setHasOpened ] = useState( false );
	const handleKey = ( { key } ) => {
		if ( [ ' ', 'return' ].includes( key ) ) {
			setHasOpened( true );
		}
	};

	return (
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
		<details
			open={ false }
			onKeyDown={ handleKey }
			onClick={ () => {
				setHasOpened( true );
			} }
		>
			<summary>
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
									/* translators: %s is an author name. */
									__( 'by %s', 'amp' ),
									author,
								) }
							</span>
						) }
						{ version && (
							<span className="site-scan-results__source-version">
								{ sprintf(
									/* translators: %s is a version number. */
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
			</summary>
			{ hasOpened && <SiteScanSourcesDetail slug={ slug } /> }
		</details>
	);
}

SiteScanSourcesListItem.propTypes = {
	author: PropTypes.string,
	name: PropTypes.string,
	slug: PropTypes.string.isRequired,
	status: PropTypes.string,
	version: PropTypes.string,
	inactiveSourceNotice: PropTypes.string,
	uninstalledSourceNotice: PropTypes.string,
};
