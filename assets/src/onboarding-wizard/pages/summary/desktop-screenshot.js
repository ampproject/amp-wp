/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Desktop } from '../../components/desktop';

export function DesktopScreenshot( { screenshot, name, description, url } ) {
	return (
		<div className="selectable selectable--bottom">

			<div className="grid grid-1-2 summary-screenshot">
				<Desktop>
					<img
						src={ screenshot }
						alt={ name }
						loading="lazy"
						decoding="async"
						height="900"
						width="1200"
					/>
				</Desktop>
				<div>
					<h3>
						{ name }
					</h3>
					<p>
						{ description }
					</p>
					<p>
						<a href={ url } target="_blank" rel="noreferrer">
							{ __( 'Learn more', 'amp' ) }
						</a>
					</p>
				</div>
			</div>

		</div>
	);
}

DesktopScreenshot.propTypes = {
	description: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	screenshot: PropTypes.string.isRequired,
	url: PropTypes.string.isRequired,
};
