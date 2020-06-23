/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useContext } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Navigation } from '../navigation-context-provider';

/**
 * Navigation component.
 *
 * @param {Object} props Component props.
 * @param {string} props.exitLink Link to exit the application.
 */
export function Nav( { exitLink } ) {
	const { activePageIndex, canGoForward, moveBack, moveForward } = useContext( Navigation );

	return (
		<div className="amp-setup-nav">
			<div className="amp-setup-nav__close">
				<Button isLink href={ exitLink }>
					{ __( 'Close', 'amp' ) }
				</Button>
			</div>
			<div className="amp-setup-nav__prev-next">
				{ 2 > activePageIndex // The first screen doesn't need to be returned to.
					? (
						<span className="amp-setup-nav__placeholder">
							{ ' ' }
						</span>
					)
					: (
						<Button
							onClick={ moveBack }
						>
							{ __( 'Previous', 'amp' ) }
						</Button>
					)
				}

				<Button
					disabled={ ! canGoForward }
					isPrimary
					onClick={ moveForward }
				>
					{ __( 'Next', 'amp' ) }
				</Button>

			</div>
		</div>
	);
}

Nav.propTypes = {
	exitLink: PropTypes.string.isRequired,
};
