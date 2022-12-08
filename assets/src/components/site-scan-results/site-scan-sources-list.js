/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Loading } from '../loading';
import { SiteScanSourcesListItem } from './site-scan-source-list-item';

/**
 * Site Scan sources list component.
 *
 * @param {Object} props                         Component props.
 * @param {Array}  props.sources                 Sources data.
 * @param {string} props.inactiveSourceNotice    Message to show next to an inactive source.
 * @param {string} props.uninstalledSourceNotice Message to show next to an uninstalled source.
 */
export function SiteScanSourcesList({
	sources,
	inactiveSourceNotice,
	uninstalledSourceNotice,
}) {
	if (sources.length === 0) {
		return <Loading />;
	}

	return (
		<ul className="site-scan-results__sources">
			{sources.map((source) => (
				<li key={source.slug} className="site-scan-results__source">
					<SiteScanSourcesListItem
						{...source}
						inactiveSourceNotice={inactiveSourceNotice}
						uninstalledSourceNotice={uninstalledSourceNotice}
					/>
				</li>
			))}
		</ul>
	);
}

SiteScanSourcesList.propTypes = {
	sources: PropTypes.array.isRequired,
	inactiveSourceNotice: PropTypes.string,
	uninstalledSourceNotice: PropTypes.string,
};
